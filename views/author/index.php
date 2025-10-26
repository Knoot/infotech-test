<?php

use app\models\Author;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Authors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (Yii::$app->user->can('createAuthor')): ?>
            <?= Html::a('Create Author', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'lastname',
            'surname',
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
