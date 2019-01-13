<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Master */

$this->title = 'Смена пароля мастера';
$this->params['breadcrumbs'][] = ['label' => 'Кабинет мастера', 'url' => ['master/kabinet']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="klient-update">
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= Html::label('Изменяются только заполненные поля. Мастера и клиенты могут менять имя пользователя раз в месяц'); ?>

    <?php 
        if (Yii::$app->session->get('role') == common\models\AuthItem::HEAD_MANAGER) {
            echo $form->field($model, 'id')->dropDownList(ArrayHelper::map($mass, 'id_manager', 'user.username'));
        }
    ?>
    
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'password')->passwordInput(['id' => 'password']) ?>

    <?= $form->field($model, 'password1')->passwordInput(['id' => 'password1']) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success', 'id' => 'signup']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
    <?php $this->registerJsFile('js/signup.js', ['depends' => 'yii\web\YiiAsset', 'position' => View::POS_END]) ?>

</div>

    