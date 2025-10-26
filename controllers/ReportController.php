<?php

namespace app\controllers;
use app\models\BookAuthor;
use yii\db\Query;

class ReportController extends \yii\web\Controller
{
    private const TOP_LIMIT = 10;

    public function actionIndex()
    {
        return $this->actionTopAuthors(date('Y'));
    }

    public function actionTopAuthors(?int $year = null)
    {
        $year = $year ?: (int) date('Y');

        $rows = (new Query())
                ->select(['a.id', 'a.lastname',  'a.name',  'a.surname', 'COUNT(b.id) AS book_count'])
                ->from(['a' => 'author'])
                ->innerJoin(['ba' => BookAuthor::tableName()], 'a.id = ba.author_id')
                ->innerJoin(['b' => 'book'], 'b.id = ba.book_id')
                ->where(['b.year' => $year])
                ->groupBy('a.id')
                ->orderBy(['book_count' => SORT_DESC])
                ->limit(self::TOP_LIMIT)
                ->all();
        ;

        return $this->render('top-authors', [
            'rows'  => $rows,
            'year'  => $year,
            'limit' => self::TOP_LIMIT,
        ]);
    }

}
