<?php

use yii\widgets\DetailView;
use yii\helpers\Html;

$this->title = 'Действия мастера';
$this->params['breadcrumbs'][] = $this->title;

/* @var $model common\models\VidChangeParametr */
?>

<div class="master-actions-index">        
    <p>
        <?= Html::a('Изменение настроек', ['update'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php 
    if (isset($model) && $model != NULL){
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [                
                'id', 'name',                 
                [
                    'label' => 'Добавляемый рейтинг за заявку, в ед.',
                    'value' => $model->reyting_add,
                ],
                [
                    'label' => 'Снимаемый рейтинг за отказ от заявки, в % от рейтинга мастера',
                    'value' => $model->reyting_delete,
                ],
                [
                    'label' => 'Процент баланса возвращаемый после отказа от заявки',
                    'value' => $model->balans_add,
                ],
                [
                    'label' => 'Процент баланса снимаемый за заявку',
                    'value' => $model->balans_delete,
                ]
            ],
        ]);
    }
    ?>       
</div>

