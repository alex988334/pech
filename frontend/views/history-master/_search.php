<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryMasterSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="history-master-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'date') ?>

    <?= $form->field($model, 'time') ?>

    <?= $form->field($model, 'id_status_history') ?>

    <?= $form->field($model, 'role') ?>

    <?php // echo $form->field($model, 'username') ?>

    <?php // echo $form->field($model, 'id_user') ?>

    <?php // echo $form->field($model, 'id_master') ?>

    <?php // echo $form->field($model, 'familiya') ?>

    <?php // echo $form->field($model, 'imya') ?>

    <?php // echo $form->field($model, 'otchestvo') ?>

    <?php // echo $form->field($model, 'id_status_on_off') ?>

    <?php // echo $form->field($model, 'vozrast') ?>

    <?php // echo $form->field($model, 'staj') ?>

    <?php // echo $form->field($model, 'reyting') ?>

    <?php // echo $form->field($model, 'id_status_work') ?>

    <?php // echo $form->field($model, 'data_registry') ?>

    <?php // echo $form->field($model, 'data_unregistry') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'mesto_jitelstva') ?>

    <?php // echo $form->field($model, 'mesto_raboti') ?>

    <?php // echo $form->field($model, 'balans') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <?php // echo $form->field($model, 'limit_zakaz') ?>

    <?php // echo $form->field($model, 'old_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
