<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ClientOrderMaster */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-order-master-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php // $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_client')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_order')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_master')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'created_at')->textInput() ?>

    <?php // $form->field($model, 'id_region')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
