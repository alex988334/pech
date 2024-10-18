<?php

use yii\web\View;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use common\models\AuthItem;
use common\widgets\ShowFields;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    <?php Pjax::begin(); ?>   
    <?php    
        $gridView = [ 'dataProvider' => $dataProvider ];  
        $massColums = [
            ['class' => 'yii\grid\SerialColumn'],    
        ];        
        
        
      /*  $filter = ArrayHelper::map($massFilters['vidRole'], 'name', 'description');
                    
                    $massColums[$last]['filter'] = $filter;
                    
                    $massColums[$last]['attribute'] = 'item_name';
                    $massColums[$last]['value'] = function ($data) use ($filter){
                     
                        return ($data['role']['item_name'] != '') ? $filter[$data['role']['item_name']] : '' ;
                    };*/
        foreach ($fields as $one){
            if ($one['name'] == 'id') {
                $massColums[] = [
                    'attribute' => 'item_name', //$one['role']['item_name'],                   
                    'label' => 'Роль', //$one['role'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'], 
                    'visible' => true,
                    'value' => function ($data) use ($massFilters){
                        return $massFilters['vidRole'][$data['role']['item_name']]['description'] ?? '';
                    },
                    'filter' => ArrayHelper::map($massFilters['vidRole'], 'name', 'description'),
                ];
            }
            
            if ($one['visibility_field'] == "0") continue;
            if ($one['name'] == 'password_hash' || $one['name'] == 'auth_key' 
                    || $one['name'] == 'password_reset_token') continue;  
            
            $massColums[] = [
                'attribute' => $one['name'],                   
                'label' => $one['alias'],                 
                'contentOptions' => ['style'=>'white-space: normal;'], 
                'visible' => true
            ];
            $last = count($massColums) - 1;
            switch ($one['name']){ 
                case 'created_at':
                    $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                    $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                        'model' => $searchModel,
                        'attribute' => 'created_at',
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ]);
                    break;
                case 'updated_at':
                    $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                    $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                        'model' => $searchModel,
                        'attribute' => 'updated_at',
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ]);
                    break;
                case 'status':
                    $massColums[$last]['filter'] = $massFilters['vidStatus'];
                    $massColums[$last]['value'] = function ($data) use ($massFilters){
                        return (key_exists($data['status'], $massFilters['vidStatus'])) ? 
                                $massFilters['vidStatus'][$data['status']] : $data['status'];
                    };
                    break;  
                case 'item_name':
                    $filter = /*ArrayHelper::merge(['' => null], */
                            ArrayHelper::map($massFilters['vidRole'], 'name', 'description');
                    
                    $massColums[$last]['filter'] = $filter;
                    
                    $massColums[$last]['attribute'] = 'item_name';
                    $massColums[$last]['value'] = function ($data) use ($filter){
                       /* debugArray($data);*/
                        return ($data['role']['item_name'] != '') ? $filter[$data['role']['item_name']] : '' ;
                    };
                    break;
            }
        }
        $massColums[] = [
            'class' => 'yii\grid\ActionColumn',
                'buttons'=>[                    
                    'reset-password' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-remove-sign"></span>', 
                            ['/site/reset'], 
                            [
                                'title' => 'Сброс пароля мастера',
                                'data' => [
                                    'method' => 'post',  
                                    'confirm' => 'Сброс пароля пользователя на стандартный пароль "PechnoyMir", продолжить?',
                                    'params' => [
                                        'type' => 2,
                                        'id' => $model['id'],                                  
                                    ]
                                ]
                            ]);                        
                    },
                    'reset-imei' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-phone"></span>', 
                                ['/site/reset'], 
                                [
                                    'title' => 'Сброс IMEI',
                                    'data' => [
                                        'method' => 'post',  
                                        'confirm' => 'Сброс IMEI пользователя (для мобильного приложения), продолжить?',
                                        'params' => [
                                            'type' => 3,
                                            'id' => $model['id'],                                  
                                        ]
                                    ]
                                ]);                        
                    },
                    'block-user' => function ($url, $model){                        
                        if ($model['status'] == \common\models\User::STATUS_BLOCKED) {
                            $name = 'glyphicon glyphicon-user';
                            $title = 'Разблокировать пользователя';
                        } else {
                            $name = 'glyphicon glyphicon-ban-circle';
                            $title = 'Заблокировать пользователя';
                        }                        
                        return Html::a('<span class="'. $name .'"></span>',
                                ['/site/block-user'],
                                [
                                    'title' => $title,
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'Вы блокируете пользователя! '
                                                . 'Пользователь не сможет даже зайти в учетную запись. Продолжить?',
                                        'params' => [
                                            'id' => $model['id']
                                        ]
                                    ]                                    
                                ]);
                    }
                ],
                'template' => '{reset-password} {reset-imei} {block-user}',
        ];
        $gridView['filterModel'] = $searchModel;
        $gridView['columns'] = $massColums;
       // debugArray($gridView);
      //  debugArray($fields);
    ?>    
    <?= GridView::widget($gridView); ?>
    
    <?php 
        if ($fields != null) {
            echo ShowFields::widget (['fields' => $fields, 
                    'blackFields' => [
                        'password_hash' => 'password_hash', 
                        'auth_key' => 'auth_key',  
                        'password_reset_token' => 'password_reset_token'
                    ]]); 
            $this->registerJsFile('@web/js/select_fields.js', [
                'dependst' => 'yii\web\YiiAsset',
                'position' => View::POS_END]);   
        }
    ?>
   
    <?php Pjax::end(); ?>    
</div>