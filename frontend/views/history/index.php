<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel common\models\HistoryKlientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'История входов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-index">
   
    <?php Pjax::begin(); ?>
   
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],    
                'id',
                [
                    'attribute' => 'item_name',
                    'label' => 'Роль',
                    'filter' => ArrayHelper::map($massFilters['vidRole'], 'name', 'description'),
                    'value' => function($data) use ($massFilters){
                        return $massFilters['vidRole'][$data['role']['item_name']]['description'];
                    }
                ],
                [
                    'attribute' => 'id_user',
                    'label' => '№ пользователя',
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::a($data['id_user'], ['/site/user', ['id' => $data['id_user']]]);
                    }
                ],                
                [
                    'attribute' => 'username',
                    'label' => 'Имя пользователя',
                    'value' => function($data){
                        return $data['user']['username'];
                    }
                ],
                [
                    'attribute' => 'action',
                    'filter' => $massFilters['vidAction'],
                ],
                [
                   // 'class' => 'yii\jui\DatePicker',
                    'attribute' => 'date',                    
                    'filter' => yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => 'date',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]),
                 //   'format' => ['date', 'php:d-m-Y'],
                ],                        
                'time',
                'ip',
            ]
        ]);
    ?>
    
    <?php Pjax::end(); ?>
</div>
