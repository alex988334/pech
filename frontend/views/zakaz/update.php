<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use mirocow\yandexmaps\Map;
use mirocow\yandexmaps\Canvas;



/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Обновление заявки №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 
    'url' => Yii::$app->urlManager->createUrl(['/zakaz/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление';
?>

    
<div class="zakazi-update">

    <?php

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zakazi-form">
    
    <?php // debugArray($model) ?>
    
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_vid_work')->dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name'))?>

    <?= $form->field($model, 'id_navik')->dropDownList(ArrayHelper::map($vid['vidNavik'], 'id', 'name')) ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'opisanie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reyting_start')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'id_status_zakaz')->dropDownList(ArrayHelper::map($vid['vidStatusZakaz'], 'id', 'name')) ?>

    <?= $form->field($model, 'zametka')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gorod')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'poselok')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ulica')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dom')->textInput() ?>

    <?= $form->field($model, 'kvartira')->textInput() ?>

    <?= $form->field($model, 'data_start')->widget(DatePicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
    ]) ?>

    <?= $form->field($model, 'data_end')->widget(DatePicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
    ]) ?>

    <?php /* '<div><a href="#" class="spoiler-title">Карта</a>
            <div id="map" class="spoiler-content">'?>
    <?php    
      /*  $map = new Map('yandex_map', [
            'center' => [55.7372, 37.6066],
            'zoom' => 10,
            // Enable zoom with mouse scroll
            'behaviors' => array('default', 'scrollZoom'),
            'type' => "yandex#map",
        ], 
        [
            // Permit zoom only fro 9 to 11
            'minZoom' => 3,
            'maxZoom' => 18,
          /*  'controls' => [
                // v 2.1
           /*     'new ymaps.control.ZoomControl({options: {size: "small"}})',
                'new ymaps.control.TrafficControl({options: {size: "small"}})',
                'new ymaps.control.GeolocationControl({options: {size: "small"}})',
                'search' => 'new ymaps.control.SearchControl({options: {size: "small"}})',
                'new ymaps.control.FullscreenControl({options: {size: "small"}})',
                'new ymaps.control.RouteEditor({options: {size: "small"}})',
            ],
             /*       'controls' => [
                        "new ymaps.control.SmallZoomControl()",
                        "new ymaps.control.TypeSelector(['yandex#map', 'yandex#satellite'])",  
                    ],                   
        ]);
        echo Canvas::widget([
                'htmlOptions' => [
                    'style' => 'height: 400px;',
                    ],
                'map' => $map,
            ]);
    ?>
    <?= '</div></div>'*/ ?>
        
    <?php // $form->field($model, 'dolgota')->textInput() ?>

    <?php // $form->field($model, 'shirota')->textInput() ?>
    
    <?php // $form->field($model, 'dolgota_change')->textInput() ?>

    <?php // $form->field($model, 'shirota_change')->textInput() ?>
    
    <?php //  $form->field($model, 'image')->label() ?>
    
    <?php if (Yii::$app->session->get('role') == 'head_manager') {
        echo $form->field($model, 'cena')->textInput(['maxlength' => true]);
    }?>

    <?php // $form->field($model, 'id_region')->dropDownList(ArrayHelper::map($vid['region'], 'id', 'name')) ?>

    <?php // $form->field($model, 'id_ocenka')->textInput(['maxlength' => true])//->dropDownList(ArrayHelper::map($vid['ocenka'], 'id', 'name')) ?>

    <?php // $form->field($model, 'otziv')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
    <?php 
        if ($model->image != null) {
            echo '<img src="/uploads/image/' . $model->image . '" ></img><br><br><hr>';
        }
    ?>
    
    <?php $form1 = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <?= $form1->field($model1, 'image_file')->label('Выберите новое изображение')->fileInput() ?>
    
    <?php // '<input name="id" hidden value="' . $model->id . '">' ?>
    
    <?= $form1->field($model1, 'id')->textInput(['value' => $model->id ]) ?>
   
    <div class="form-group">
        <?= Html::submitButton('Сохранить новое изображение', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
    
    

</div>

</div>
<?php //  $this->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru_RU', ['depends' => 'yii\web\YiiAsset', 'position' => yii\web\View::POS_HEAD]) ?>
<?php  //$this->registerJsFile('@web/js/map.js', ['depends' => 'https://api-maps.yandex.ru/2.1/?lang=ru_RU']) ?>

<?php // $this->registerJsFile('@web/js/map.js', ['depends' => 'yii\web\YiiAsset']) ?>

