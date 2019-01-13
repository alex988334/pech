<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\Klient */

$this->title = 'Создание клиента';
$this->params['breadcrumbs'][] = ['label' => 'Klients', 
    'url' => Yii::$app->urlManager->createUrl(['/klient/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="klient-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_klient')->hiddenInput() ?>

    <?= $form->field($model, 'imya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'familiya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'otchestvo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'vozrast')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
