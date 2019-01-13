<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Master */

$this->title = 'Обновление мастера: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Мастера', 
        'url' => Yii::$app->urlManager->createUrl(['/master/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление';
?>
<div class="master-update">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_master')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_status_on_off')->dropDownList(ArrayHelper::map($vid['vidStatusOnOff'], 'id', 'name')) ?>

    <?= $form->field($model, 'vozrast')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'staj')->textInput() ?>    

    <?= $form->field($model, 'id_status_work')->dropDownList(ArrayHelper::map($vid['vidStatusWork'], 'id', 'name')) ?>

    <?php // $form->field($model, 'data_registry')->textInput() ?>

    <?= $form->field($model, 'data_unregistry')->textInput() ?>    

    <?= $form->field($model, 'mesto_jitelstva')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mesto_raboti')->textInput(['maxlength' => true]) ?>
    
    <?php // $form->field($model, 'limit_zakaz')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'id_region')->textInput(['maxlength' => true]); ?>
    
    <?php          
        if (Yii::$app->session->get('role') == common\models\User::HEAD_MANAGER) {
            
            echo $form->field($model, 'phone')->textInput(['maxlength' => true]); 
            echo $form->field($model, 'reyting')->textInput(['maxlength' => true]);
            echo $form->field($model, 'balans')->textInput(['maxlength' => true]); 
       //     echo $form->field($model, 'id_region')->textInput(['maxlength' => true]);
       //     echo $form->field($model, 'limit_zakaz')->textInput(['maxlength' => true]); 
        }
    ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
