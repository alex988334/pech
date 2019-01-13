<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\KlientVsZakaz */

$this->title = 'Update Klient Vs Zakaz: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Klient Vs Zakazs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="klient-vs-zakaz-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
