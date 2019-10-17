<?php

use yii\db\Migration;

/**
 * Class m191017_071224_add_table_message
 */
class m191017_071224_add_table_message extends Migration
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

        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()->comment('Message header'),
            'text' => $this->string()->notNull()->comment('Message text'),
            'sender_id' => $this->integer()->notNull()->comment('Sender message'),
            'recipient_id' => $this->integer()->notNull()->comment('Recipient message'),
            'status' => $this->tinyInteger()->defaultValue(0)->comment('0 - not read, 1 - read')
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%message}}');
    }

}
