<?php

namespace app\controllers;

use app\models\Author;
use app\models\Subscription;
use app\service\SubscriptionService;
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

        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('subscribe_referrer', Yii::$app->request->referrer);
        }

        if (Yii::$app->request->isPost) {
            $service = Yii::$container->get(SubscriptionService::class);
            if ($subscription->load(Yii::$app->request->post()) && $service->subscribe($subscription)) {
                $url = Yii::$app->session->get('subscribe_referrer', ['book/index']);
                Yii::$app->session->remove('subscribe_referrer');

                Yii::$app->session->setFlash('success', 'Subscribed!');

                return $this->redirect($url);
            } else {
                Yii::$app->session->setFlash('error', 'Subscribe error!');
            }
        }

        return $this->render('subscribe', [
            'author' => $author,
            'model'  => $subscription,
        ]);
    }

}
