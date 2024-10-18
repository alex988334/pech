<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;



/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Создание нового менеджера';
$this->params['breadcrumbs'][] = ['label' => 'Менеджеры', 
    'url' => Yii::$app->urlManager->createUrl(['/manager/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="manager-create">

<?php
/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="manager-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?php // $form->field($manager, 'id')->hiddenInput() ?>
    
    <?php //$form->field($manager, 'id_manager')->textInput([/*'maxlength' => true, */'readonly' => true]) ?>    
    
    <?= $form->field($manager, 'familiya')->textInput(/*['maxlength' => true]*/) ?>
    
    <?= $form->field($manager, 'imya')->textInput(/*['maxlength' => true]*/) ?>
    
    <?= $form->field($manager, 'otchestvo')->textInput(/*['maxlength' => true]*/) ?>
    
    <?= $form->field($manager, 'id_region')->dropDownList(ArrayHelper::map($massFilters['vidRegion'], 'id', 'name')) ?>
    
    <?= $form->field($manager, 'phone1')->textInput(/*['maxlength' => true]*/) ?>

    <?= $form->field($manager, 'phone2')->textInput(/*['maxlength' => true]*/) ?>
    
    <?= $form->field($manager, 'phone3')->textInput(/*['maxlength' => true]*/) ?>
    
    <?= $form->field($user, 'username')->textInput() ?>
    
    <?= $form->field($user, 'password_hash')->textInput() ?>
    
    <?= $form->field($user, 'status')->dropDownList($massFilters['vidStatus']) ?>
    
    <?= $form->field($user, 'email')->textInput() ?> 
    
    <?= $form->field($role, 'item_name')->dropDownList($massFilters['vidRole']) ?>   
         
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
    
</div>
</div>


