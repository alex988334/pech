<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Печной мир';
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login">    

    <div  style="width: 400px; margin-left: auto; margin-right: auto; text-align: center;">
        
        <h1><?= Html::encode($this->title) ?></h1>
        <p>Для продолжения авторизуйтесь</p>
        
        <!--div class="col-lg-5"-->
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?php // $form->field($model, 'rememberMe')->checkbox() ?>

                <!--div style="color:#999;margin:1em 0">
                    Если вы забыли <?php // Html::a('пароль', ['site/request-password-reset']) ?>.
                </div>

                <div class="form-group"-->
                    <?= Html::submitButton('Вход', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                <!--/div-->

            <?php ActiveForm::end(); ?>
        <!--/div-->
    </div>
</div>
