<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\VidInitializationMaster */

$this->title = 'Обновление настроек' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Инициализация мастера', 
    'url' => Yii::$app->urlManager->createUrl(['/master-init/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
   
<div class="master-init-update">

<div class="master-init-form">
    
    <?php // debugArray($model) ?>    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'start_reyting')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'start_balans')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'limit_zakaz')->textInput(['maxlength' => true]) ?>
  
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
</div>
</div>


