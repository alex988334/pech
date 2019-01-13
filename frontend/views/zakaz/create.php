<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Новая заявка';
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 
    'url' => Yii::$app->urlManager->createUrl(['/zakaz/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zakazi-create">

    <?php
    /* @var $this yii\web\View */
    /* @var $model common\models\Zakazi */
    /* @var $form yii\widgets\ActiveForm */
    ?>

<div class="zakazi-form">
    
    <?php // debugArray($model) ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_vid_work')->dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name')) ?>

    <?= $form->field($model, 'id_navik')->dropDownList(ArrayHelper::map($vid['vidNavik'], 'id', 'name')) ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cena')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'opisanie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reyting_start')->textInput(['maxlength' => true]) ?>
    
    <?php // $form->field($model, 'id_status_zakaza')->dropDownList(ArrayHelper::map($vid['statusZakaza'], 'id', 'name')) ?>

    <?= $form->field($model, 'zametka')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gorod')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'poselok')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ulica')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dom')->textInput() ?>

    <?= $form->field($model, 'kvartira')->textInput() ?>

    <?php /* $form->field($model, 'data_registry')->widget(DataPicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd'
    ]) */ ?>
    
    <?= $form->field($model, 'data_start')->widget(DatePicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
    ]) ?>

    <?= $form->field($model, 'data_end')->widget(DatePicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
    ]) ?>
    
    <?php // map ?>

    <?= $form->field($model, 'dolgota')->textInput() ?>

    <?= $form->field($model, 'shirota')->textInput() ?>

    <?php // $form->field($model, 'dolgota_isk')->textInput() ?>

    <?php // $form->field($model, 'shirota_isk')->textInput() ?>

    <?php // $form->field($model, 'image')->textInput(['maxlength' => true]) ?>
    

    <?php // $form->field($model, 'id_region')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'ocenka')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'otziv')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

</div>
