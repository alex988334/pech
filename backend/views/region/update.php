<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use yii\web\View;



/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Обновление региона №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Регионы', 
    'url' => Yii::$app->urlManager->createUrl(['/region/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

    
<div class="region-update">

    <?php

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
    
    $url = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=4ec92947-0754-4056-90f0-4c6568e0ade1';
    $this->registerJsFile($url, ['position' => View::POS_HEAD, 'depends' => 'yii\web\YiiAsset']);
    
    $this->registerJsVar('moove', TRUE, View::POS_BEGIN);
    $this->registerJsVar('dolgota', $model->dolgota, View::POS_BEGIN);
    $this->registerJsVar('shirota', $model->shirota, View::POS_BEGIN);        
    $this->registerJsFile('/js/map.js', ['position' => View::POS_END]);
?>
<div class="region-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true])
            //dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name'))?>

    <?= $form->field($model, 'parent_id')
            ->dropDownList(ArrayHelper::map($massFilters['vidRegion'], 'id', 'name')) ?>
    
    <?= $form->field($model, 'dolgota')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shirota')->textInput(['maxlength' => true]) ?>
  
    <div id="map" style="min-width: 600px; min-height: 400px"></div>
    
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
</div>
</div>


