<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ClientOrderMaster */

$this->title = 'Обновление клиент-заявка-мастер: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Клиент-заявка-мастер', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление';
?>
<div class="client-order-master-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
