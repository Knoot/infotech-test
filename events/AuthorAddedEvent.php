<?php
namespace app\events;

use app\models\Author;
use yii\base\Event;

final class AuthorAddedEvent extends Event
{

    public function __construct(public Author $author, ?array $config = [])
    {
        parent::__construct($config);
    }
}
