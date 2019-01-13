<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Klient */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="klient-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_klient')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vozrast')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_status_on_off')->textInput() ?>

    <?= $form->field($model, 'reyting')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'balans')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_region')->textInput() ?>

    <?= $form->field($model, 'old_id')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
