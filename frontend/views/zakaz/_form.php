<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zakazi-form">
    
    <?php debugArray($model) ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_vid')->dropDownList(ArrayHelper::map($model->idV, 'id', 'name'))/*->textInput(['maxlength' => true]) */?>

    <?= $form->field($model, 'nazvanie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cena')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'opisanie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reyting_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'zametka')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gorod')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'poselok')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ulica')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dom')->textInput() ?>

    <?= $form->field($model, 'kvartira')->textInput() ?>

    <?= $form->field($model, 'data_registry')->textInput() ?>

    <?= $form->field($model, 'data_start')->textInput() ?>

    <?= $form->field($model, 'data_end')->textInput() ?>

    <?= $form->field($model, 'dolgota')->textInput() ?>

    <?= $form->field($model, 'shirota')->textInput() ?>

    <?= $form->field($model, 'dolgota_isk')->textInput() ?>

    <?= $form->field($model, 'shirota_isk')->textInput() ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_region')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ocenka')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otziv')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
