<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\rest\Controller;
use yii\web\HttpException;

class UserController extends Controller
{
    /**
     * Registers new user
     *
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionSignUp()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->save()) {
                return $this->asJson($model->generateToken());
            }

            if ($model->hasErrors()) {
                throw new HttpException(422, implode(', ', $model->firstErrors));
            }
        }
    }

    /**
     *User authentication
     *
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $user = User::findByEmail($post['email']);
        if ($user === null) {
            throw new HttpException('422', 'User not found');
        }

        if (isset($post['password']) && $user->validatePassword($post['password'])) {
            return $this->asJson($user->generateToken());
        }
        else
        {
            throw new HttpException('422', 'Incorrect password');
        }
    }


}