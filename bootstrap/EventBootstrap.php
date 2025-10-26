<?php
namespace app\bootstrap;

use app\events\AuthorAddedEvent;
use app\models\Book;
use app\service\SubscriptionService;
use yii\base\BootstrapInterface;
use yii\base\Event;
use Yii;

class EventBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Event::on(Book::class, Book::EVENT_AUTHOR_ADDED, function (AuthorAddedEvent $event) {
            $subject = 'New book notification';
            $text    = "New book from {$event->author->getFullName()}";

            // TODO queue
            Yii::$container->get(SubscriptionService::class)->notify($event->author, $subject, $text);
        });
    }
}
