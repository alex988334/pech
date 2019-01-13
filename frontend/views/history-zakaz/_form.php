<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryZakaz */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="history-zakaz-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <?= $form->field($model, 'time')->textInput() ?>

    <?= $form->field($model, 'id_status_history')->textInput() ?>

    <?= $form->field($model, 'role')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_user')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_zakaz')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_vid_work')->textInput() ?>

    <?= $form->field($model, 'id_navik')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cena')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'opisanie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reyting_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'zametka')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gorod')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'poselok')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ulica')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dom')->textInput() ?>

    <?= $form->field($model, 'kvartira')->textInput() ?>

    <?= $form->field($model, 'id_status_zakaz')->textInput() ?>

    <?= $form->field($model, 'id_shag')->textInput() ?>

    <?= $form->field($model, 'data_registry')->textInput() ?>

    <?= $form->field($model, 'data_start')->textInput() ?>

    <?= $form->field($model, 'data_end')->textInput() ?>

    <?= $form->field($model, 'dolgota')->textInput() ?>

    <?= $form->field($model, 'shirota')->textInput() ?>

    <?= $form->field($model, 'dolgota_change')->textInput() ?>

    <?= $form->field($model, 'shirota_change')->textInput() ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_region')->textInput() ?>

    <?= $form->field($model, 'id_ocenka')->textInput() ?>

    <?= $form->field($model, 'otziv')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
