<?php

use yii\widgets\DetailView;
use yii\helpers\Html;

$this->title = 'Инициализация мастера';
$this->params['breadcrumbs'][] = $this->title;

/* @var $model common\models\VidInitializationMaster */
?>

<div class="master-init-index">       
    <p>
        <?= Html::a('Изменение настроек', ['update'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php 
    if (isset($model) && $model != NULL){
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [                
                'id', 'name', 'start_reyting', 'start_balans', 'limit_zakaz'  
            ],
        ]);
    }
    ?>  
</div>

