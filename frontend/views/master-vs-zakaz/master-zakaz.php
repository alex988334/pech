<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\MasterVsZakaz */

$this->title = 'Новая связка мастер-заявка';
$this->params['breadcrumbs'][] = ['label' => 'Мастера и заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

//debugArray($model);
//debugArray($massId);
?>
<div class="master-vs-zakaz-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_zakaz')->dropDownList($massId) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
