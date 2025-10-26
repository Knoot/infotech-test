<?php
use borales\extensions\phoneInput\PhoneInput;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Subscription $model */
$this->title = 'Subscribe to ' . $author->getFullName();
?>
<?php $form = ActiveForm::begin(); ?>

<div class="form-group">
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->widget(PhoneInput::class, [
        'jsOptions' => [
            'preferredCountries' => ['us', 'ru', 'ua', 'by'],
            'nationalMode'       => false,
        ],
    ]) ?>

    <?= Html::submitButton('Subscribe', ['class' => 'btn btn-success']) ?>
</div>
<?php ActiveForm::end(); ?>