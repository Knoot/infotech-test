<?php

namespace app\models;

use app\base\validators\IsbnValidator;
use app\service\cache\TopAuthorsCacheService;
use Biblys\Isbn\Isbn;
use Biblys\Isbn\IsbnParsingException;
use Yii;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string $isbn
 * @property string|null $description
 * @property string|null $photo
 *
 * @property Author[] $authors
 * @property BookAuthor[] $bookAuthors
 */
class Book extends \yii\db\ActiveRecord
{
    public const EVENT_AUTHOR_ADDED = 'book.authorAdded';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'photo'], 'default', 'value' => null],
            [['title', 'year', 'isbn'], 'required'],
            [['year'], 'integer', 'min' => 1400, 'max' => (int) date('Y')],
            [['description'], 'string'],
            [['title', 'photo'], 'string', 'max' => 255],
            [['title'], 'filter', 'filter' => 'trim'],
            [['photo'], 'url', 'defaultScheme' => 'http'],
            [['isbn'], 'unique'],
            [['isbn'], IsbnValidator::class],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'title'       => 'Title',
            'year'        => 'Year',
            'isbn'        => 'Isbn',
            'description' => 'Description',
            'photo'       => 'Photo',
        ];
    }

    /**
     * Gets query for [[Authors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])->viaTable('book_author', ['book_id' => 'id']);
    }

    public function getIsbnFormatted(): string
    {
        if (!$this->isbn) {
            return '';
        }

        try {
            return Isbn::convertToIsbn13($this->isbn);
        } catch (IsbnParsingException $e) {
            return $this->isbn;
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->isbn = Isbn::convertToIsbn13($this->isbn);
            $this->isbn = preg_replace('/[^0-9]/', '', $this->isbn);

            return true;
        }

        return false;
    }

    /**
     * Gets query for [[BookAuthors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['book_id' => 'id']);
    }

    public function afterDelete()
    {
        parent::afterDelete();

        TopAuthorsCacheService::invalidateByYears([$this->year]);
    }

}
