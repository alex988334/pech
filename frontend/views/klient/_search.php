<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\KlientSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="klient-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_klient') ?>

    <?= $form->field($model, 'imya') ?>

    <?= $form->field($model, 'familiya') ?>

    <?= $form->field($model, 'otchestvo') ?>

    <?php // echo $form->field($model, 'vozrast') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'id_status_on_off') ?>

    <?php // echo $form->field($model, 'reyting') ?>

    <?php // echo $form->field($model, 'balans') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <?php // echo $form->field($model, 'old_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
