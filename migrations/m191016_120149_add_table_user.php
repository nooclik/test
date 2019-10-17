<?php

use yii\db\Migration;

/**
 * Class m191016_120149_add_table_user
 */
class m191016_120149_add_table_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'phone_number' => $this->string(20)
        ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
