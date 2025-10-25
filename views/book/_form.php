<?php

use app\models\Author;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="book-form">
    <?php $form         = ActiveForm::begin(); ?>
    <?php $modelsAuthor = empty($modelsAuthor) ? [new Author()] : $modelsAuthor ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isbn')->textInput([
        'value'     => $model->isbn,
        'maxlength' => true,
    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper',
        'widgetBody'      => '.container-authors',
        'widgetItem'      => '.author-item',
        'limit'           => 10,
        'min'             => 1,
        'insertButton'    => '.add-author',
        'deleteButton'    => '.remove-author',
        'model'           => $modelsAuthor[0],
        'formId'          => $form->id,
        'formFields'      => ['lastname', 'name', 'surname'],
    ]); ?>

    <div class="container-authors">
        <?php foreach ($modelsAuthor as $i => $modelAuthor): ?>
            <div class="author-item panel panel-default">
                <div class="panel-heading">
                    <button type="button" class="add-author btn btn-success btn-xs">+</button>
                    <button type="button" class="remove-author btn btn-danger btn-xs">−</button>
                </div>
                <div class="panel-body">
                    <?php if (!$modelAuthor->isNewRecord): ?>
                        <?= Html::activeHiddenInput($modelAuthor, "[{$i}]id") ?>
                    <?php endif; ?>
                    <?= $form->field($modelAuthor, "[{$i}]lastname")->textInput() ?>
                    <?= $form->field($modelAuthor, "[{$i}]name")->textInput() ?>
                    <?= $form->field($modelAuthor, "[{$i}]surname")->textInput() ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php DynamicFormWidget::end(); ?>

    <?= $form->field($model, 'photo')->textInput(['maxlength' => true]) ?>

    <?php if ($model->photo): ?>
        <img src="<?= htmlspecialchars($model->photo) ?>" alt="Book photo">
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>