<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ClientOrderMasterSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-order-master-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_client') ?>

    <?= $form->field($model, 'id_order') ?>

    <?= $form->field($model, 'id_master') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
