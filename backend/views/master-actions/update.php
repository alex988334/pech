<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Обновление настроек' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Действия мастера', 
    'url' => Yii::$app->urlManager->createUrl(['/master-actions/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

    
<div class="master-actions-update">

    <?php

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="master-actions-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'reyting_add')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'reyting_delete')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'balans_add')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'balans_delete')->textInput(['maxlength' => true]) ?>
  
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
</div>
</div>


