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
    
    <?php    
        $vidWork = ArrayHelper::map($filters['vidWork'], 'id', 'name');
        $vidNavik = ArrayHelper::map($filters['vidNavik'], 'id', 'name');
        $cl = [];
        foreach ($clients as $one) {
            $cl[$one['id']] = '№' . $one['id'] . ', логин: ' . $one['username'] . ', ' 
                    . $one['imya'] . ' ' . $one['familiya'] . ' ' . $one['otchestvo']; 
        }
        
        echo $form->field($model, 'id_client')->dropDownList($cl);
        
        $ord = [];
        foreach ($orders as $one) {            
            $ord[$one['id']] = '№' . $one['id'] . ', название: ' . $one['name'] 
                    . ', цена: ' . $one['cena'] . ', вид: ' . $vidWork[$one['id_vid_work']] 
                    . ', навык: ' . $vidNavik[$one['id_navik']] . ', рейтинг: ' . $one['reyting_start'];
        }
     //   echo $model->id_order ?? 'null';
        if ($model->id_order) { 
            echo $form->field($model, 'id_order')->dropDownList($ord, 
                    ['options' => [$model->id_order => ['selected' => true]]]);
        } else {
            echo $form->field($model, 'id_order')->dropDownList($ord);
        }
    ?>
    
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
