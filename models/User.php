<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\validators\EmailValidator;
use yii\web\HttpException;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $email
 * @property string $password_hash
 * @property string $phone_number
 * @property string $password
 */

class User extends ActiveRecord implements IdentityInterface
{
    public $password;

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['email', 'phone_number'], 'required'],
            [['email'], 'email'],
            [['password', 'phone_number'], 'string'],
            ['email', 'unique', 'targetClass' => 'app\models\User', 'message' => 'The user with this email is registered in the system'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $users = User::find()->asArray()->all();
        foreach ($users as $user) {
            if ($user['id'] === (string)$token->getClaim('uid')) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     * @throws HttpException
     */
    public static function findByEmail($email)
    {
        $validator = new EmailValidator();

        if (!$validator->validate($email, $error)) {
            throw new HttpException('422', $error);
        }
        return static::findOne(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @throws \yii\base\Exception
     */
    public function setPassword()
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
    }

    /**
     * Generate token for entry
     *
     * @return array
     */
    public function generateToken()
    {
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();

        $token = $jwt->getBuilder()
            ->issuedBy('http://test.loc')
            ->permittedFor('http://test.loc')
            ->identifiedBy('4f1g23a12aa', true)
            ->issuedAt($time)
            ->expiresAt($time + 600)
            ->withClaim('uid', $this->id)
            ->getToken($signer, $key);

        return [
            'token' => (string)$token,
        ];
    }

    /**
     * Event before saving
     *
     * @param bool $insert
     * @return bool
     * @throws \yii\base\Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->setPassword();
            return true;
        }
        return false;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
}
