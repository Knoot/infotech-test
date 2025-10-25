<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property int $author_id
 * @property string $email
 * @property string|null $phone
 *
 * @property Author $author
 */
class Subscription extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone'], 'default', 'value' => null],
            [['author_id', 'email'], 'required'],
            [['author_id'], 'integer'],
            [['email'], 'string', 'max' => 25],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 16],
            [['author_id', 'email'], 'unique', 'targetAttribute' => ['author_id', 'email']],
            [['author_id', 'phone'], 'unique', 'targetAttribute' => ['author_id', 'phone']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'author_id' => 'Author ID',
            'email'     => 'Email',
            'phone'     => 'Phone',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->phone) {
            $this->phone = preg_replace('/\D+/', '', $this->phone);
        }

        return true;
    }

}
