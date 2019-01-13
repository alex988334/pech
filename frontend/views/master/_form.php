<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Master */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_master')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_status_on_off')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vozrast')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'staj')->textInput() ?>

    <?= $form->field($model, 'reyting')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_status_work')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_registry')->textInput() ?>

    <?= $form->field($model, 'data_unregistry')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mesto_jitelstva')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mesto_raboti')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'balans')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_region')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'limit_zakaz')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
