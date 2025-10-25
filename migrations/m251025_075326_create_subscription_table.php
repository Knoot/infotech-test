<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscription}}`.
 */
class m251025_075326_create_subscription_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscription}}', [
            'id'        => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'email'     => $this->string()->notNull(),
            'phone'     => $this->string(16),
        ]);

        $this->addForeignKey(
            'fk_subscription-author',
            '{{%subscription}}',
            'author_id',
            '{{%author}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'ux_subscription-author_email',
            '{{%subscription}}',
            ['author_id', 'email'],
            true
        );

        $this->createIndex(
            'ux_subscription-author_phone',
            '{{%subscription}}',
            ['author_id', 'phone'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%subscription}}');
    }
}
