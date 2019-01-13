<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\KlientVsZakaz */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="klient-vs-zakaz-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_klient')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_zakaz')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
