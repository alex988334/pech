<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\ClientOrderMaster */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-order-master-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['readonly' => true]); //['readonly' => true] ?>
       
    <?= $form->field($model, 'id_client')->textInput(['readonly' => true]); ?>
    <?= $form->field($model, 'id_order')->textInput(['readonly' => true]); ?>

    <?php    
        $massMasters = [];
        foreach ($free as $one) {            
            $massMasters[$one['id']] = '№' . $one['id'] . ', ' . $one['fio'] 
                    . ', рейтинг: ' . $one['reyting'] . ', телефон: ' . $one['phone'];
        }
        
        echo $form->field($model, 'id_master')->dropDownList($massMasters);
    ?>
    
    <?php // $form->field($model, 'created_at')->textInput() ?>

    <?php // $form->field($model, 'id_region')->textInput()*/ ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
