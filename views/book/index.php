<?php

use app\models\Book;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Books';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Book', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'title',
            'year',
            [
                'attribute' => 'isbn',
                'label'     => 'ISBN',
                'value'     => function ($model) {
                    return $model->getIsbnFormatted();
                }
            ],
            'description:ntext',
            [
                'label'  => 'Authors',
                'format' => 'raw',
                'value'  => function (Book $model) {
                        if (!$model->authors) {
                            return null;
                        }

                        $lines = [];
                        foreach ($model->authors as $author) {
                            $fio = htmlspecialchars($author->getFullName());

                            $lines[] = Html::tag('div', $fio, ['class' => 'author-item']);
                        }

                        return implode('', $lines);
                    },
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Book $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
