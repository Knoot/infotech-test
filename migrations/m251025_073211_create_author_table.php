<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%author}}`.
 */
class m251025_073211_create_author_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%author}}', [
            'id'       => $this->primaryKey(),
            'name'     => $this->string()->notNull(),
            'lastname' => $this->string()->notNull(),
            'surname'  => $this->string()->notNull()->defaultValue(''),
        ]);

        $this->createIndex(
            'idx_author_fullname_unique',
            '{{%author}}',
            ['lastname', 'name', 'surname'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%author}}');
    }
}
