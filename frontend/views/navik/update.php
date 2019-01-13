<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\MasterWorkNavik */

$this->title = 'Update Master Work Navik: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Навыки мастеров', 
    'url' => Yii::$app->urlManager->createUrl(['/navik/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновление навыка';
?>
<div class="master-work-navik-update">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true, 'disabled' => true]); ?>
    
    <?= $form->field($model, 'id_master')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <?= $form->field($model, 'id_vid_work')->dropDownList(ArrayHelper::map($vid['vidWork'], 'id', 'name'), ['disabled' => true]) ?>

    <?= $form->field($model, 'id_vid_navik')->dropDownList(ArrayHelper::map($vid['vidNavik'], 'id', 'name')) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php /* $this->render('_form', [
        'model' => $model,
    ]) */?>

</div>
