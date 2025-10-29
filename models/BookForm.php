<?php

namespace app\models;

use app\base\Model;
use app\events\AuthorAddedEvent;
use app\models\Book;
use app\models\Author;
use app\service\cache\TopAuthorsCacheService;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use Yii;

class BookForm extends \yii\base\Model
{
    public Book $book;

    /** @var Author[] */
    public $authors = [];

    public function __construct(?Book $book = null, $config = [])
    {
        $this->book    = $book ?: new Book();
        $this->authors = $this->book->authors ?: [new Author()];
        parent::__construct($config);
    }

    public function load($data, $formName = null): bool
    {
        $loaded = $this->book->load($data, $formName);
        if (!$loaded) {
            return false;
        }

        $authors       = $this->loadAuthorsFromData($data);
        $this->authors = $this->mergeWithExistingAuthors($authors);

        return true;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        return $this->book->validate($attributeNames, $clearErrors) && Model::validateMultiple($this->authors, $attributeNames);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var ?int */
        $oldYear     = $this->book->getOldAttribute('year');
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$this->book->save(false)) {
                throw new Exception('Failed to save book');
            }

            $uniqueAuthors = $this->normalizeAuthors($this->authors);

            [$incomingIds, $newAuthorIds, $authorMap] = $this->persistAuthors($uniqueAuthors);
            [$toAdd, $toDelete]                       = $this->calculateAuthorDiff($incomingIds);
            $pendingEvents                            = $this->linkAuthors($toAdd, $authorMap, $newAuthorIds);
            $this->unlinkAuthors($toDelete);

            $transaction->commit();

            $this->finalizeSave($toAdd, $toDelete, $oldYear, $pendingEvents);

            return true;

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            $this->addError('book', $e->getMessage());
            return false;
        }
    }
    public function getRenderParams(): array
    {
        return [
            'model'        => $this->book,
            'modelsAuthor' => $this->authors,
        ];
    }
    private function normalizeAuthors(array $authors): array
    {
        $seen = [];
        $out  = [];

        foreach ($authors as $a) {
            $key = $this->buildAuthorKey($a->name ?? null, $a->lastname ?? null, $a->surname ?? null);
            if ($key === '') {
                continue;
            }
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[]      = $a;
        }

        return $out;
    }

    private function loadAuthorsFromData(array $data): array
    {
        $existingAuthors = ArrayHelper::index($this->authors, 'id');
        $authors         = Model::createMultiple(Author::class, $existingAuthors);
        Model::loadMultiple($authors, $data);

        return $authors;
    }

    /**
     * @param Author[] $authors
     * @return Author[]
     */
    private function mergeWithExistingAuthors(array $authors): array
    {
        $lookup = [];

        foreach ($authors as $index => $author) {
            if (!$author->isNewRecord || !$author->name || !$author->lastname) {
                continue;
            }

            $name     = trim($author->name);
            $lastname = trim($author->lastname);
            $surname  = trim((string) $author->surname);

            $key = $this->buildAuthorKey($name, $lastname, $surname);
            if ($key === '') {
                continue;
            }

            $lookup[$key]['indexes'][] = $index;
            $lookup[$key]['criteria'] ??= [
                'name'     => $name,
                'lastname' => $lastname,
                'surname'  => $surname,
            ];
        }

        if (!$lookup) {
            return $authors;
        }

        $conditions = array_column($lookup, 'criteria');
        $query      = Author::find();

        if (count($conditions) === 1) {
            $query->andWhere($conditions[0]);
        } else {
            array_unshift($conditions, 'or');
            $query->andWhere($conditions);
        }

        $found = ArrayHelper::index($query->all(), function (Author $author) {
            return $this->buildAuthorKey($author->name, $author->lastname, $author->surname);
        });

        foreach ($lookup as $key => $data) {
            if (!isset($found[$key])) {
                continue;
            }

            foreach ($data['indexes'] as $index) {
                $authors[$index] = $found[$key];
            }
        }

        return $authors;
    }

    private function buildAuthorKey(?string $name, ?string $lastname, ?string $surname): string
    {
        $parts = [
            trim((string) $name),
            trim((string) $lastname),
            trim((string) $surname),
        ];

        return mb_strtolower(trim(implode(' ', array_filter($parts, static fn($part) => $part !== ''))));
    }

    /**
     * @return array{0: int[], 1: array<int,bool>, 2: array<int,Author>}
     */
    private function persistAuthors(array $authors): array
    {
        $incomingIds = $newAuthorIds = [];
        $authorMap = [];

        foreach ($authors as $author) {
            if ($author->isNewRecord || $author->getDirtyAttributes()) {
                $author->save(false);
                $newAuthorIds[$author->id] = true;
            }

            $incomingIds[]          = $author->id;
            $authorMap[$author->id] = $author;
        }

        return [$incomingIds, $newAuthorIds, $authorMap];
    }

    /**
     * @return array{0: int[], 1: int[]}
     */
    private function calculateAuthorDiff(array $incomingIds): array
    {
        $currentIds = ArrayHelper::getColumn($this->book->authors, 'id');

        $toAdd    = array_values(array_diff($incomingIds, $currentIds));
        $toDelete = array_values(array_diff($currentIds, $incomingIds));

        return [$toAdd, $toDelete];
    }

    /**
     * @param array<int,bool> $newAuthorIds
     * @param array<int,Author> $authorMap
     * @return array<string,AuthorAddedEvent[]>
     */
    private function linkAuthors(array $toAdd, array $authorMap, array $newAuthorIds): array
    {
        $pendingEvents = [];

        foreach ($toAdd as $id) {
            if ($author = $authorMap[$id] ?? null) {
                $this->book->link('authors', $author);

                if (!isset($newAuthorIds[$author->id])) {
                    $pendingEvents[Book::EVENT_AUTHOR_ADDED][] = new AuthorAddedEvent($author);
                }
            }
        }

        return $pendingEvents;
    }

    private function unlinkAuthors(array $toDelete): void
    {
        if (!$toDelete) {
            return;
        }

        $authorsById = ArrayHelper::index($this->book->authors, 'id');

        foreach ($toDelete as $id) {
            if (!isset($authorsById[$id])) {
                continue;
            }

            $this->book->unlink('authors', $authorsById[$id], true);
        }
    }

    /**
     * @param array<string,AuthorAddedEvent[]> $pendingEvents
     */
    private function finalizeSave(array $toAdd, array $toDelete, ?int $oldYear, array $pendingEvents): void
    {
        $authorsChanged = !empty($toAdd) || !empty($toDelete);
        $yearChanged    = ($oldYear !== null) && ($oldYear !== (int) $this->book->year);

        if ($authorsChanged || $yearChanged) {
            $invalidateCacheYears = [(int) $this->book->year];

            if ($yearChanged && $oldYear !== null) {
                $invalidateCacheYears[] = (int) $oldYear;
            }

            $invalidateCacheYears = array_values(array_unique($invalidateCacheYears));

            if ($invalidateCacheYears) {
                TopAuthorsCacheService::invalidateByYears($invalidateCacheYears);
            }
        }

        foreach ($pendingEvents as $name => $events) {
            foreach ($events as $event) {
                $this->book->trigger($name, $event);
            }
        }
    }
}
