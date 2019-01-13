<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\HistoryKlient */

$this->title = 'Create History Klient';
$this->params['breadcrumbs'][] = ['label' => 'History Klients', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-klient-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
