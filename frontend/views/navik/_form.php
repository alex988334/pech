<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\MasterWorkNavik */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-work-navik-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_master')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_vid_work')->textInput() ?>

    <?= $form->field($model, 'id_vid_navik')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
