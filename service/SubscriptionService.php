<?php

namespace app\service;

use app\models\Author;
use app\service\smsPilot\SMSPilotService;
use Yii;

class SubscriptionService
{
    public function __construct(
        private SMSPilotService $smsService,
    ) {
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