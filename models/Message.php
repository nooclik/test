<?php

namespace app\models;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string $title Message header
 * @property string $text Message text
 * @property int $sender_id Sender message
 * @property int $recipient_id Recipient message
 * @property int $status 0 - not read, 1 - read
 * @property int $email sender email
 * @property int $type type message, sender or sent
 */
class Message extends \yii\db\ActiveRecord
{
    public $email;
    public $type;

    const STATUS_NOT_READ = 0;
    const STATUS_READ = 1;
    const STATUS = [
        self::STATUS_NOT_READ,
        self::STATUS_READ
    ];

    const TYPE_RECEIVED = 'received';
    const TYPE_SENT = 'sent';
    const TYPE = [
        self:: TYPE_SENT,
        self::TYPE_RECEIVED
    ];

    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_INSERT] = ['title', 'text', 'email'];
        $scenarios[self::SCENARIO_UPDATE] = ['title', 'text'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'text', 'email'], 'required'],
            [['email'], 'email'],
            [['sender_id', 'recipient_id', 'status'], 'integer'],
            [['title', 'text'], 'string', 'max' => 255],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sender_id' => 'id']],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['recipient_id' => 'id']],
        ];
    }

    /**
     * If not read, mark as read
     */
    public function checkStatus()
    {
        if ($this->status == Message::STATUS_NOT_READ) {
            $this->scenario = self::SCENARIO_UPDATE;
            $this->status = Message::STATUS_READ;
            $this->save(false);
        }
    }

    /**
     * Event before saving
     *
     * @param bool $insert
     * @return bool
     * @throws HttpException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $recipient = User::findOne(['email' => $this->email]);
                if ($recipient === null) {
                    throw new HttpException('422', 'Recipient not found');
                }
                $this->recipient_id = $recipient->getId();
                $this->sender_id = Yii::$app->user->identity->getId();
            }
            return true;
        }
        return false;
    }
}
