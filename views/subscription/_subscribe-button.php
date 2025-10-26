<?php
/** @var int $authorId */
?>

<?= yii\helpers\Html::a(
    'Subscribe',
    ['subscription/subscribe', 'authorId' => $authorId, 'referrer' => Yii::$app->request->url],
    ['class' => 'btn btn-sm btn-success ml-1']
) ?>