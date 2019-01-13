<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;


/* @var $this yii\web\View */
/* @var $model common\models\Master */

$this->title = 'Создание мастера';
$this->params['breadcrumbs'][] = ['label' => 'Мастера', 'url' => Yii::$app->urlManager->createUrl(['/master/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;

//debugArray(['in' => $in]);
?>
<div class="master-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_master')->hiddenInput() ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'id_status_on_off')->dropDownList(ArrayHelper::map($vid['vidStatusOnOff'], 'id', 'name')) ?>

    <?= $form->field($model, 'vozrast')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'staj')->textInput() ?>

    <?php // $form->field($model, 'reyting')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_status_work')->dropDownList(ArrayHelper::map($vid['vidStatusWork'], 'id', 'name')) ?>

    <?php // $form->field($model, 'data_registry')->textInput() ?>

    <?php // $form->field($model, 'data_unregistry')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mesto_jitelstva')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mesto_raboti')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'balans')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'id_region')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'limit_zakaz')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
