<?php

namespace app\models;

use app\service\cache\TopAuthorsCacheService;
use Yii;

/**
 * This is the model class for table "author".
 *
 * @property int $id
 * @property string $name
 * @property string $lastname
 * @property string $surname
 *
 * @property BookAuthor[] $bookAuthors
 * @property Book[] $books
 * @property Subscription[] $subscriptions
 */
class Author extends \yii\db\ActiveRecord
{
    /** @var int[] */
    private $bookYears = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'author';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['surname'], 'default', 'value' => ''],
            [['name', 'lastname'], 'required'],
            [['name', 'lastname', 'surname'], 'string', 'max' => 255],
            [
                ['name', 'lastname', 'surname'],
                'unique',
                'targetAttribute' => ['name', 'lastname', 'surname'],
                'filter'          => function ($query) {
                    if (!$this->isNewRecord) {
                        $query->andWhere(['not', ['id' => $this->id]]);
                    }
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'       => 'ID',
            'lastname' => 'Lastname',
            'name'     => 'Name',
            'surname'  => 'Surname',
        ];
    }

    /**
     * Gets query for [[BookAuthors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Books]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])->viaTable('book_author', ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    public function getFullName()
    {
        return trim("{$this->lastname} {$this->name} {$this->surname}");
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!empty($changedAttributes)) {
            $this->clearCache($this->getBookYears());
        }
    }

    public function beforeDelete()
    {
        $this->bookYears = $this->getBookYears();

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->clearCache($this->bookYears);
    }

    private function getBookYears()
    {
        return $this->getBooks()
            ->select('year')
            ->distinct()
            ->column()
        ;
    }

    private function clearCache(array $years)
    {
        TopAuthorsCacheService::invalidateByYears($years);
    }
}
