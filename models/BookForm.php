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

    /** @var int[] */
    private array $deletedAuthorIds = [];

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

        $oldIds          = ArrayHelper::map($this->authors, 'id', 'id');
        $existingAuthors = ArrayHelper::index($this->authors, 'id');
        $this->authors   = Model::createMultiple(Author::class, $existingAuthors);
        Model::loadMultiple($this->authors, $data);
        $newIds                 = array_filter(ArrayHelper::map($this->authors, 'id', 'id'));
        $this->deletedAuthorIds = array_values(array_diff($oldIds, $newIds));

        foreach ($this->authors as $i => $author) {
            if ($author->isNewRecord && $author->name && $author->lastname) {

                $existing = Author::find()
                    ->andWhere([
                        'name'     => trim($author->name),
                        'lastname' => trim($author->lastname),
                        'surname'  => trim($author->surname),
                    ])
                    ->one();

                if ($existing) {
                    $this->authors[$i] = $existing;
                }
            }
        }

        return true;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        return $this->book->validate() && Model::validateMultiple($this->authors);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var ?int */
        $oldYear       = $this->book->getOldAttribute('year');
        $transaction   = Yii::$app->db->beginTransaction();
        $pendingEvents = [];

        try {
            if (!$this->book->save(false)) {
                throw new Exception('Failed to save book');
            }

            $uniqueAuthors = $this->normalizeAuthors($this->authors);

            $incomingIds = $newAuthorIds = [];
            foreach ($uniqueAuthors as $author) {
                if ($author->isNewRecord || $author->getDirtyAttributes()) {
                    $author->save(false);
                    $newAuthorIds[$author->id] = true;
                }
                $incomingIds[] = $author->id;
            }

            $currentIds = ArrayHelper::getColumn($this->book->authors, 'id');

            $toAdd    = array_values(array_diff($incomingIds, $currentIds));
            $toDelete = array_values(array_unique(array_merge(
                array_diff($currentIds, $incomingIds),
                $this->deletedAuthorIds
            )));

            foreach ($toAdd as $id) {
                if ($author = Author::findOne($id)) {
                    $this->book->link('authors', $author);

                    if (!isset($newAuthorIds[$author->id])) {
                        $pendingEvents[Book::EVENT_AUTHOR_ADDED][] = new AuthorAddedEvent($author);
                    }
                }
            }

            if ($toDelete) {
                Yii::$app->db->createCommand()
                    ->delete('{{%book_author}}', [
                        'book_id'   => $this->book->id,
                        'author_id' => $toDelete,
                    ])
                    ->execute();
            }

            $transaction->commit();

            $authorsChanged = !empty($toAdd) || !empty($toDelete);
            $yearChanged    = ($oldYear !== null) && ($oldYear !== (int) $this->book->year);

            $invalidateCacheYears = [];

            if ($authorsChanged || $yearChanged) {
                $invalidateCacheYears[] = (int) $this->book->year;

                if ($yearChanged) {
                    $invalidateCacheYears[] = $oldYear;
                }

                TopAuthorsCacheService::invalidateByYears($invalidateCacheYears);
            }

            foreach ($pendingEvents as $name => $event) {
                $this->book->trigger($name, $event);
            }

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
            $key = mb_strtolower(trim(($a->name ?? '') . ' ' . ($a->lastname ?? '') . ' ' . ($a->surname ?? '')));
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
}
