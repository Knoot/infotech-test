<?php

namespace app\controllers;

use app\models\Author;
use app\models\Book;
use app\models\BookAuthor;
use app\service\cache\TopAuthorsCacheService;
use Yii;
use yii\db\Query;

class ReportController extends \yii\web\Controller
{
    private const TOP_LIMIT             = 10;
    private const CACHE_TOP_AUTHORS_TTL = 86400;

    public function actionIndex()
    {
        return $this->actionTopAuthors(date('Y'));
    }

    public function actionTopAuthors(?int $year = null)
    {
        $year = $year ?: (int) date('Y');

        $cacheKey = TopAuthorsCacheService::getCacheKey(['year' => $year]);

        $rows = Yii::$app->cache->getOrSet(
            $cacheKey,
            function () use ($year) {
                return (new Query())
                    ->select(['a.id', 'a.lastname', 'a.name', 'a.surname', 'COUNT(b.id) AS book_count'])
                    ->from(['a' => Author::tableName()])
                    ->innerJoin(['ba' => BookAuthor::tableName()], 'a.id = ba.author_id')
                    ->innerJoin(['b' => Book::tableName()], 'b.id = ba.book_id')
                    ->where(['b.year' => $year])
                    ->groupBy('a.id')
                    ->orderBy(['book_count' => SORT_DESC])
                    ->limit(self::TOP_LIMIT)
                    ->all();
            },
            self::CACHE_TOP_AUTHORS_TTL
        );

        return $this->render('top-authors', [
            'rows'  => $rows,
            'year'  => $year,
            'limit' => self::TOP_LIMIT,
        ]);
    }

}
