<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book}}`.
 */
class m251025_072556_create_book_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id'          => $this->primaryKey(),
            'title'       => $this->string()->notNull(),
            'year'        => $this->smallInteger()->notNull(),
            'isbn'        => $this->char(13)->notNull()->unique(),
            'description' => $this->text(),
            'photo'       => $this->string(),
        ]);

        $this->createIndex('idx_book_year', '{{%book}}', 'year');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%book}}');
    }
}
