<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryZakazSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="history-zakaz-search">

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

    <?php // echo $form->field($model, 'id_zakaz') ?>

    <?php // echo $form->field($model, 'id_vid_work') ?>

    <?php // echo $form->field($model, 'id_navik') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'cena') ?>

    <?php // echo $form->field($model, 'opisanie') ?>

    <?php // echo $form->field($model, 'reyting_start') ?>

    <?php // echo $form->field($model, 'zametka') ?>

    <?php // echo $form->field($model, 'gorod') ?>

    <?php // echo $form->field($model, 'poselok') ?>

    <?php // echo $form->field($model, 'ulica') ?>

    <?php // echo $form->field($model, 'dom') ?>

    <?php // echo $form->field($model, 'kvartira') ?>

    <?php // echo $form->field($model, 'id_status_zakaz') ?>

    <?php // echo $form->field($model, 'id_shag') ?>

    <?php // echo $form->field($model, 'data_registry') ?>

    <?php // echo $form->field($model, 'data_start') ?>

    <?php // echo $form->field($model, 'data_end') ?>

    <?php // echo $form->field($model, 'dolgota') ?>

    <?php // echo $form->field($model, 'shirota') ?>

    <?php // echo $form->field($model, 'dolgota_change') ?>

    <?php // echo $form->field($model, 'shirota_change') ?>

    <?php // echo $form->field($model, 'image') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <?php // echo $form->field($model, 'id_ocenka') ?>

    <?php // echo $form->field($model, 'otziv') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
