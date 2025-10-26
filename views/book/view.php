<?php

use app\models\Book;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var app\models\Book $model */
/** @var yii\web\View $this */

$this->title                   = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="book-view">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method'  => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'id',
            'title',
            'year',
            'isbn',
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

                        $subscribeButton = $this->render('/subscription/_subscribe-button', [
                            'authorId' => $author->id,
                        ]);

                        $lines[] = Html::tag('div', $fio . ' ' . $subscribeButton, ['class' => 'author-item']);
                    }


                    return implode('', $lines);
                },
            ],
            [
                'attribute' => 'photo',
                'format'    => 'raw',
                'value'     => function ($model) {
                    return $model->photo
                        ? '<img src="' . htmlspecialchars($model->photo) . '" alt="Book photo">'
                        : null;
                },
            ],
        ],
    ]) ?>

</div>