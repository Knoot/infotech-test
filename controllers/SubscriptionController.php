<?php

namespace app\controllers;

use app\models\Author;
use app\models\Subscription;
use Yii;
use yii\web\NotFoundHttpException;

class SubscriptionController extends \yii\web\Controller
{
    public function actionSubscribe($authorId)
    {
        $author = Author::findOne(['id' => $authorId]);

        if (!$author) {
            throw new NotFoundHttpException('Author does not exist.');
        }

        $subscription = new Subscription(['author_id' => $author->id]);

        if ($subscription->load(Yii::$app->request->post())) {
            $existing = Subscription::find()
                ->where([
                    'author_id' => $author->id,
                ])
                ->andWhere(['or',
                    ['email' => $subscription->email],
                    ['phone' => $subscription->phone],
                ])
                ->one()
            ;

            if ($existing || $subscription->save()) {
                Yii::$app->session->setFlash('success', 'Subscribed!');
                return $this->redirect(Yii::$app->request->get('referrer', 'book/index'));
            }
        }

        return $this->render('@app/views/subscription/subscribe', [
            'author' => $author,
            'model'  => $subscription,
        ]);
    }

}
