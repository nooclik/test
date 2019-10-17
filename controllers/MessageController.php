<?php

namespace app\controllers;


use app\models\Message;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class MessageController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }

    /**
     * Send message
     *
     * @throws HttpException
     */
    public function actionSend()
    {
        $model = new Message();
        $model->scenario = Message::SCENARIO_INSERT;

        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->save()) {
                return ['message' => 'The message has been successfully sent'];
            }

            if ($model->hasErrors()) {
                throw new HttpException(422, implode(', ', $model->firstErrors));
            }
        }
    }

    /**
     * Get all user message
     *
     * @param $type
     * @param $status
     * @return ActiveDataProvider
     * @throws HttpException
     */
    public function actionList($type, $status)
    {
        if (!in_array($type, Message::TYPE) || !in_array($status, Message::STATUS)) {
            throw new HttpException('422', 'Incorrect parameters');
        }

        if ($type == Message::TYPE_SENT) {
            $query = Message::find()->where([
                'sender_id' => Yii::$app->user->identity->getId(),
                'status' => $status
            ]);
        } else {
            $query = Message::find()->where([
                'recipient_id' => Yii::$app->user->identity->getId(),
                'status' => $status
            ]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query
        ]);

        if ($provider->count == 0) {
            throw new NotFoundHttpException('Messages not found');
        }

        return $provider;
    }

    /**
     * Display one user message
     *
     * @param $id
     * @return Message|null
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $message = Message::findOne($id);
        if ($message === null) {
            throw new NotFoundHttpException('Message not found');
        }
        $message->checkStatus();
        return $message;
    }

    /**
     * Delete user message
     *
     * @param $id
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $message = Message::findOne($id);
        if ($message === null) {
            throw new NotFoundHttpException('Message not found');
        }

        if ($message->sender_id != Yii::$app->user->identity->getId()) {
            throw new ForbiddenHttpException("You cannot delete another user's message");
        }

        if ($message->delete()) {
            return ['message' => 'Successfully deleted'];
        }
    }
}