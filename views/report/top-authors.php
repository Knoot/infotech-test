<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $rows */
/** @var int $year */
/** @var int $limit */
$this->title = "Top {$limit} authors for $year";
?>
<h1>
    <?= Html::encode($this->title) ?>
</h1>

<form method="get" action="">
    <label>Year:</label>
    <input type="number" name="year" value="<?= (int) $year ?>" min="1400" max="<?= date('Y') ?>">
    <button type="submit">Show</button>
</form>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>#</th>
            <th>Author</th>
            <th>Books</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $r): ?>
            <tr>
                <td>
                    <?= $i + 1 ?>
                </td>
                <td>
                    <?= Html::encode("{$r['lastname']} {$r['name']} {$r['surname']} ") ?>
                    
                </td>
                <td>
                    <?= (int) $r['book_count'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>