<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\SignupForm */

use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Создание нового пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Мастера', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Заполните все поля</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?php // $form->field($model, 'email') ?>

                <?= $form->field($model, 'password')->passwordInput(['id' => 'password']) ?>
            
                <?= $form->field($model, 'password1')->passwordInput(['id' => 'password1']) ?>

                <div class="form-group">
                    <?= Html::submitButton('Регистрация', ['class' => 'btn btn-primary', 'name' => 'signup-button', 'id' => 'signup']) ?>
                </div>

            <?php ActiveForm::end(); ?>
            
            <?php          
                $this->registerJsFile('js/signup.js', ['depends' => 'yii\web\YiiAsset', 'position' => View::POS_END])
            ?>
        </div>
    </div>
</div>
