<?php

namespace app\service\smsPilot;

use Yii;

class SMSPilotService
{
    private SMSPilot $smsPilot;

    public function __construct(
        private string $apiKey,
        private string|false $from,
    ) {
        $this->smsPilot = new SMSPilot($apiKey, from: $from);
    }

    public function send(string $phone, string $text, ?\DateTime $sendTime = null): array
    {
        if (YII_ENV_DEV) {
            Yii::info(
                [
                    'phone'    => $phone,
                    'text'     => $text,
                    'sendTime' => $sendTime?->format('Y-m-d H:i:s'),
                ],
                'sms'
            );

            return [
                'id'     => 1,
                'phone'  => $phone,
                'price'  => 0,
                'status' => 0,
            ];
        }

        $status = $this->smsPilot->send(
            $phone,
            $text,
            send_datetime: $sendTime ? $sendTime->getTimestamp() : false
        );

        return $status ?: [
            'error' => $this->smsPilot->error,
        ];
    }
}