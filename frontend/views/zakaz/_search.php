<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ZakaziSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zakazi-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_vid') ?>

    <?= $form->field($model, 'id_navik') ?>

    <?= $form->field($model, 'nazvanie') ?>

    <?= $form->field($model, 'cena') ?>

    <?php // echo $form->field($model, 'opisanie') ?>

    <?php // echo $form->field($model, 'reyting_start') ?>

    <?php // echo $form->field($model, 'zametka') ?>

    <?php // echo $form->field($model, 'gorod') ?>

    <?php // echo $form->field($model, 'poselok') ?>

    <?php // echo $form->field($model, 'ulica') ?>

    <?php // echo $form->field($model, 'dom') ?>

    <?php // echo $form->field($model, 'kvartira') ?>

    <?php // echo $form->field($model, 'id_status_zakaza') ?>

    <?php // echo $form->field($model, 'id_shag') ?>

    <?php // echo $form->field($model, 'data_registry') ?>

    <?php // echo $form->field($model, 'data_start') ?>

    <?php // echo $form->field($model, 'data_end') ?>

    <?php // echo $form->field($model, 'dolgota') ?>

    <?php // echo $form->field($model, 'shirota') ?>

    <?php // echo $form->field($model, 'dolgota_isk') ?>

    <?php // echo $form->field($model, 'shirota_isk') ?>

    <?php // echo $form->field($model, 'image') ?>

    <?php // echo $form->field($model, 'id_region') ?>

    <?php // echo $form->field($model, 'ocenka') ?>

    <?php // echo $form->field($model, 'otziv') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
