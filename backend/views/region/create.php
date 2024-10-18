<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use yii\web\View;



/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Создание нового региона';
$this->params['breadcrumbs'][] = ['label' => 'Регионы', 
    'url' => Yii::$app->urlManager->createUrl(['/region/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php 
    //  yandex maps API
 //   debugArray($model);

    $url = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=4ec92947-0754-4056-90f0-4c6568e0ade1';
    $this->registerJsFile($url, ['position' => View::POS_HEAD, 'depends' => 'yii\web\YiiAsset']);
    
    $this->registerJsVar('moove', TRUE, View::POS_BEGIN);
    $this->registerJsVar('dolgota', $model->dolgota, View::POS_BEGIN);
    $this->registerJsVar('shirota', $model->shirota, View::POS_BEGIN);
  
    $this->registerJsFile('/js/map.js', ['position' => View::POS_END,
        'depends' => 'yii\web\YiiAsset']);
?>

<div class="region-create">

    <?php

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="region-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?php // $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true])
            //dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name'))?>

    <?php
        $mass = ArrayHelper::merge([0 => 'Нет родительского региона'], 
                ArrayHelper::map($massFilters['vidRegion'], 'id', 'name'));
       
        echo $form->field($model, 'parent_id')->dropDownList($mass) ?>
    
    <?= $form->field($model, 'dolgota')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shirota')->textInput(['maxlength' => true]) ?>
  
    <div id="map" style="min-width: 600px; min-height: 400px"></div>
    
    <?php 
        $this->registerJsVar('moove', TRUE, View::POS_BEGIN);
        $this->registerJsVar('dolgota', $model->dolgota, View::POS_BEGIN);
        $this->registerJsVar('shirota', $model->shirota, View::POS_BEGIN);      
        $this->registerJsFile('/js/map.js', ['position' => View::POS_END]);        
    ?>
    
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>  
</div>
</div>


