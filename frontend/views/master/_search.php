<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\MasterSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_mastera') ?>

    <?= $form->field($model, 'familiya') ?>

    <?= $form->field($model, 'imya') ?>

    <?= $form->field($model, 'otchestvo') ?>

    <?php // echo $form->field($model, 'id_status_podklucheniya') ?>

    <?php // echo $form->field($model, 'vozrast') ?>

    <?php // echo $form->field($model, 'staj') ?>

    <?php // echo $form->field($model, 'reyting') ?>

    <?php // echo $form->field($model, 'id_status_work') ?>

    <?php // echo $form->field($model, 'data_registry') ?>

    <?php // echo $form->field($model, 'data_uvolneniya') ?>

    <?php // echo $form->field($model, 'telefon') ?>

    <?php // echo $form->field($model, 'mesto_jitelstva') ?>

    <?php // echo $form->field($model, 'mesto_raboti') ?>

    <?php // echo $form->field($model, 'balans') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <?php // echo $form->field($model, 'limit_zakaz') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
