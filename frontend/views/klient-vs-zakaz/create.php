<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\KlientVsZakaz */

$this->title = 'Новая связка клиент-заявка';
$this->params['breadcrumbs'][] = ['label' => 'Клиенты и заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="klient-vs-zakaz-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_klient')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'id_zakaz')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
