<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;



/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Создание нового навыка';
$this->params['breadcrumbs'][] = ['label' => 'Навыки', 
    'url' => Yii::$app->urlManager->createUrl(['/skill/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

    
<div class="skill-create">

    <?php

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="work-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?php // $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true])
            //dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name'))?>

    <?= $form->field($model, 'sort')->textInput(['maxlength' => true]) ?>   
  
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
</div>
</div>


