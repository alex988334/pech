<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
use yii\web\View;
use common\models\FileManager;

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */

$this->title = 'Обновление заявки №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 
    'url' => Yii::$app->urlManager->createUrl(['/zakaz/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = ['label' => '№' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление';
?>

<?php 
    //  yandex maps API
    $url = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=4ec92947-0754-4056-90f0-4c6568e0ade1';
    $this->registerJsFile($url, ['position' => View::POS_HEAD]);
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
    
    <?= $form->field($model, 'id')->textInput(['maxlength' => true, 'readonly' => true]) ?>

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
        'options' => [
            'class' => 'form-control',
        ],
    ]) ?>

    <?= $form->field($model, 'data_end')->widget(DatePicker::class, [
        'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
        'options' => [
            'class' => 'form-control',
        ],
    ]) ?>

            
    <?= $form->field($model, 'dolgota')->textInput(['style' => ['background-color' => '#ff8e85']]) ?>

    <?= $form->field($model, 'shirota')->textInput(['style' => ['background-color' => '#ff8e85']]) ?>
    
    <div id="map" style="min-width: 600px; min-height: 400px"></div>
    
    <?php 
        $this->registerJsVar('moove', TRUE, View::POS_BEGIN);
        $this->registerJsVar('dolgota', $model->dolgota, View::POS_BEGIN);
        $this->registerJsVar('shirota', $model->shirota, View::POS_BEGIN);
        $this->registerJsVar('dolgota_change', $model->dolgota_change, View::POS_BEGIN);
        $this->registerJsVar('shirota_change', $model->shirota_change, View::POS_BEGIN);
        $this->registerJsFile('/js/map.js', ['position' => View::POS_END]);
    ?>
      
    <?= $form->field($model, 'dolgota_change')->label('Долгота искаженная')->textInput([
            'style' => ['background-color' => '#5fa3ff']
        ]) ?>

    <?= $form->field($model, 'shirota_change')->label('Широта искаженная')->textInput([
            'style' => ['background-color' => '#5fa3ff']
        ]) ?>
        
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
            echo Html::tag('img', '', ['src' => '/' . FileManager::FILES . '/' 
                    . FileManager::ADDRESS_ORDERS . '/' . $model->image]);
         //   echo '<img src="/' . FileManager::FILES . '/' . FileManager::ADDRESS_ORDERS 
         //           . '/' . $model->image . '" ></img><br><br><hr>';
        }
    ?>
    
    <?php $form1 = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <?= $form1->field($model1, 'image_file')->label('Выберите новое изображение')->fileInput(); ?>
    
    <?php // '<input name="id" hidden value="' . $model->id . '">' ?>
    
    <?= $form1->field($model1, 'id')->label('№ заявки')->textInput(['value' => $model->id, 'readonly' => true]); ?>
   
    <div class="form-group">
        <?= Html::submitButton('Сохранить новое изображение', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
    
    

</div>

</div>
<?php //  $this->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru_RU', ['depends' => 'yii\web\YiiAsset', 'position' => yii\web\View::POS_HEAD]) ?>
<?php  //$this->registerJsFile('@web/js/map.js', ['depends' => 'https://api-maps.yandex.ru/2.1/?lang=ru_RU']) ?>

<?php // $this->registerJsFile('@web/js/map.js', ['depends' => 'yii\web\YiiAsset']) ?>

