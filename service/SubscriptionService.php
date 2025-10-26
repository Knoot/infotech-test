<?php

namespace app\service;

use app\models\Author;
use app\models\Subscription;
use app\service\smsPilot\SMSPilotService;
use Yii;

class SubscriptionService
{
    public function __construct(
        private SMSPilotService $smsService,
    ) {
    }

    public function subscribe(Subscription $subscription): bool
    {
        $existing = Subscription::find()
            ->where(['author_id' => $subscription->author_id])
            ->andWhere(['or',
                ['email' => $subscription->email],
                ['phone' => $subscription->phone],
            ])
            ->one()
        ;

        if ($existing) {
            return true;
        }

        return $subscription->save();
    }

    public function notify(Author $author, string $subject, string $text)
    {
        foreach ($author->subscriptions as $subscription) {
            $this->sendEmail($subscription->email, $subject, $text);

            if ($subscription->phone) {
                $this->sendSms($subscription->phone, $text);
            }
        }
    }

    private function sendEmail(string $emailTo, $subject, string $text)
    {
        Yii::$app->mailer
            ->compose()
            ->setTo($emailTo)
            ->setFrom([Yii::$app->params['adminEmail'] => 'Book Catalog'])
            ->setSubject($subject)
            ->setTextBody($text)
            ->send()
        ;
    }

    private function sendSms(string $phone, string $text)
    {
        $this->smsService->send($phone, $text);
    }
}