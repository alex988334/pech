<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Klient */

$this->title = 'Обновление клиента: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Клиенты', 
    'url' => Yii::$app->urlManager->createUrl(['/klient/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление';
?>
<div class="klient-update">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_klient')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vozrast')->textInput() ?>    

    <?= $form->field($model, 'id_status_on_off')->dropDownList(ArrayHelper::map($vid['vidStatusOnOff'], 'id', 'name')) ?>
    
            
    
    <?php 
        if (Yii::$app->session->get('role') == 'head_manager') {
            echo $form->field($model, 'balans')->textInput(['maxlength' => true]);
            echo $form->field($model, 'phone')->textInput(['maxlength' => true]);
        }  
    ?>
    <?php // $form->field($model, 'reyting')->textInput(['maxlength' => true]) ?>
    <?php // $form->field($model, 'id_region')->textInput() ?>
    

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
