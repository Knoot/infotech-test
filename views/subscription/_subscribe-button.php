<?php
/** @var int $authorId */
?>

<?= yii\helpers\Html::a(
    'Subscribe',
    ['subscription/subscribe', 'authorId' => $authorId],
    ['class' => 'btn btn-sm btn-success ml-1']
) ?>