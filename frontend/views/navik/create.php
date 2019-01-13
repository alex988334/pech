<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\MasterWorkNavik */

$this->title = 'Создание нового навыка';
$this->params['breadcrumbs'][] = ['label' => 'Навыки мастеров', 
    'url' => Yii::$app->urlManager->createUrl(['/navik/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-work-navik-create">

    <?php $form = ActiveForm::begin(); ?>

    <?php 
        if (isset($massMaster)) {
            echo $form->field($model, 'id_master')->dropDownList(ArrayHelper::map($massMaster, 'id_master', 'id_master'));
        } else {
            echo $form->field($model, 'id_master')->textInput(['maxlength' => true]);
        } 
    ?>  

    <?php // $form->field($model, 'id')->textInput(['maxlength' => true]); ?>  
    
    <?= $form->field($model, 'id_vid_work')->dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name')) ?>

    <?= $form->field($model, 'id_vid_navik')->dropDownList(ArrayHelper::map($vid['vidNavik'], 'id', 'name')) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Создать', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
    <?php /* $this->render('_form', [
        'model' => $model,
    ])*/ ?>

</div>
