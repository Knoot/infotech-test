<?php

use yii\helpers\Html;

/** @var app\models\Author[] $modelsAuthor */
/** @var app\models\Book $model */
/** @var yii\web\View $this */

$this->title                   = 'Create Book';
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-create">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= $this->render('_form', [
        'model'        => $model,
        'modelsAuthor' => $modelsAuthor,
    ]) ?>

</div>