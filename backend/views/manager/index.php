<?php

use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Менеджеры';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="manager-index">    
    <?php // Pjax::begin(); ?>
    <p>
        <?= Html::a('Создание нового менеджера', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php if (isset($dataProvider)){
        echo GridView::widget([
        'dataProvider' => $dataProvider,   
        'filterModel'=> $searchModel,
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],                 
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id_manager',                   
                    'label' => '№',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'username',                   
                    'label' => 'Логин',
                    'value' => 'user.username',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'status',                   
                    'label' => 'Статус',
                    'value' => function($data) use ($massFilters) {             //  заменяем номер статуса его названием
                        if (key_exists($data->user->status, $massFilters['vidStatus'])){
                            return $massFilters['vidStatus'][$data->user->status];
                        } else return $data->user->status;
                    },
                    'filter' => $massFilters['vidStatus'],
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'item_name',                   
                    'label' => 'Роль',
                    'value' => function($data) use ($massFilters){              //  заменяем идентификатор роли ее названием
                        if (key_exists($data->role->item_name, $massFilters['vidRole'])){
                            return $massFilters['vidRole'][$data->role->item_name]['description'];
                        } else return $data->role->item_name;
                    },   
                    'filter' => ArrayHelper::map($massFilters['vidRole'], 'name', 'description')  
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'familiya',                   
                    'label' => 'Фамилия',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'imya',                   
                    'label' => 'Имя',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'otchestvo',                   
                    'label' => 'Отчество',
                ],                
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'name',                   
                    'label' => 'Регион',
                    'value' => function ($data){                    
                        if ($data->region != NULL){
                            return $data->region->name;
                        } else return $data->id_region;
                    },                  
                    'filter' => ArrayHelper::map($massFilters['vidRegion'], 'name', 'name')
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'email',                   
                    'label' => 'Почта',
                    'value' => 'user.email',   
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'phone1',                   
                    'label' => 'Телефон',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'phone2',                   
                    'label' => 'Телефон',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'phone3',                   
                    'label' => 'Телефон',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'delete' => function($url, $data){                      //  кнопка удаления менеджера
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', 
                                        ['delete'], 
                                        [   
                                            'title' => 'Удаление',
                                            'data' => [
                                                'method' => 'post', 
                                                'params' => [ 'id' => $data->id],
                                                'confirm' => 'Удаление менеджера. Продолжить?',
                                            ]
                                        ]);            
                        },
                        'update' => function($url, $data){                      //  кнопка изменения данных менеджера
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 
                                        ['update'], 
                                        [   
                                            'title' => 'Обновление',
                                            'data' => [
                                                'method' => 'get', 
                                                'params' => [ 'id' => $data->id_manager],                                                
                                            ]
                                        ]);            
                        }
                    ],
                    'template' => '{update} {delete}', 
                ]
        ],      
        ]);
    }
    ?>   
    
    <?php // Pjax::end(); ?>  
</div>

