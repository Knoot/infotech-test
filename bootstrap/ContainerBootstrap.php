<?php
namespace app\bootstrap;

use app\service\smsPilot\SMSPilotService;
use yii\base\BootstrapInterface;
use Yii;

class ContainerBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Yii::$container->set(
            SMSPilotService::class,
            fn() => Yii::$app->smsPilot
        );
    }
}
