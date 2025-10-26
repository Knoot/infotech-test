<?php

use app\models\Book;
use yii\helpers\Html;
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
        <?php if (Yii::$app->user->can('createBook')): ?>
            <?= Html::a('Create Book', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
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
                'class'          => ActionColumn::class,
                'template'       => '{view} {update} {delete}',
                'visibleButtons' => [
                    'update' => function ($model, $key, $index) {
                            return Yii::$app->user->can('updateBook', ['book' => $model]);
                        },
                    'delete' => function ($model, $key, $index) {
                            return Yii::$app->user->can('deleteBook', ['book' => $model]);
                        },
                ],
            ],
        ],
    ]); ?>


</div>
