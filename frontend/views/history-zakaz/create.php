<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\HistoryZakaz */

$this->title = 'Create History Zakaz';
$this->params['breadcrumbs'][] = ['label' => 'History Zakazs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-zakaz-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
