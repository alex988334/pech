<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\AuthItem;
use common\models\VidRegion;
use yii\helpers\ArrayHelper;
use common\widgets\ShowFields;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ZakaziSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//debugArray(Yii::$app->session->get('selectedRegion'));
//debugArray(Yii::$app->session->get('selectedVid'));
//debugArray(Yii::$app->session->get('activeZakaz'));
//debugArray(Yii::$app->session->get('masterNavik'));

$role = Yii::$app->session->get('role');

$this->title = 'Заявки';
//$this->params['breadcrumbs'][] = ['label' => 'Кабинет мастера', 'url' => ['/master/kabinet']];
if ($role == 'master') $this->params['breadcrumbs'][] = ['label' => 'Заявки по группам', 'url' => ['/zakazi/vid']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zakazi-index">

    <?php
  //  debugArray($fields);
  /*      debugArray([
            Yii::$app->session->get('role'), 
            Yii::$app->session->get('region'),
            $dataProvider->models,
            $parametrs
                ]);
  //    */  
    ?>      
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) ?
                Html::a('Добавить новую заявку', ['create'], ['class' => 'btn btn-success']) : ''; ?>                
        <?php 
            if (Yii::$app->session->get('invisibleExecutedOrders') == FALSE) {
                $class = 'btn btn-success';
                $buttonLabel = 'Показать завершенные';
            } else {
                $class = 'btn btn-warning';
                $buttonLabel = 'Скрыть завершенные';
            } 
            if (Yii::$app->session->get('invisibleBlockedOrders') == FALSE) {
                $class1 = 'btn btn-success';
                $buttonLabel1 = 'Показать заблокированные';
            } else {
                $class1 = 'btn btn-warning';
                $buttonLabel1 = 'Скрыть заблокированные';
            }
        ?>             
        <?= ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) ? 
                Html::a($buttonLabel, ['show-executed-orders'], ['class' => $class]) : ''; ?>
        <?= ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) ? 
                Html::a($buttonLabel1, ['show-blocked-orders'], ['class' => $class1]) : ''; ?>
        
    </p>
    
    <?php Pjax::begin(); ?>
    <?php 
        $gridView = [ 'dataProvider' => $dataProvider ];  
        if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
            $massColums = [
                ['class' => 'yii\grid\SerialColumn'],    
            ];        
            foreach ($fields as $one){
                if ($one['visibility_field'] == "0") continue;
                
                $massColums[] = [
                    'attribute' => $one['name'],                   
                    'label' => $one['alias'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'], 
                    'visible' => true
                ];
                $last = count($massColums) - 1;
                switch ($one['name']){ 
                    case 'id':
                        $massColums[$last]['format'] = 'raw';
                        $massColums[$last]['value'] = function ($data) {
                            return Html::a($data['id'], ['/client-order-master/index'], 
                                    [
                                        'title' => 'клиент-заявка-мастер',
                                        'data' => [
                                            'method' => 'post', 
                                            'params' => [
                                                'id_order' => $data['id'],
                                            ]
                                        ]
                                    ]);                        
                        };
                        break;
                    case 'id_vid_work': 
                        $massColums[$last]['attribute'] = 'vid_work_name';                   
                        $massColums[$last]['label'] = 'Вид работ';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidWork'], 'name', 'name');
                        break;
                    case 'id_navik':
                        $massColums[$last]['attribute'] = 'navik_name';                   
                        $massColums[$last]['label'] = 'Требуемый навык';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidNavik'], 'name', 'name');
                        break;
                    case 'id_status_zakaz':
                        $massColums[$last]['attribute'] = 'status_zakaz_name';                   
                        $massColums[$last]['label'] = 'Статус';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusZakaz'], 'name', 'name');
                        break;
                    case 'id_shag':
                        $massColums[$last]['attribute'] = 'shag_name';                   
                        $massColums[$last]['label'] = 'Шаг';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidShag'], 'name', 'name');
                        break;
                    case 'id_region':
                        $massColums[$last]['attribute'] = 'region_name';                   
                        $massColums[$last]['label'] = 'Регион';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidRegion'], 'name', 'name');
                        break;
                    case 'id_ocenka':
                        $massColums[$last]['attribute'] = 'ocenka_name';                   
                        $massColums[$last]['label'] = 'Оценка';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidOcenka'], 'name', 'name');
                        break; 
                    case 'data_registry':
                        $massColums[$last]['value'] = function ($data){ 
                            return $data['data_registry'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => 'data_registry',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;
                    case 'data_start':
                        $massColums[$last]['value'] = function ($data){ 
                            return $data['data_start'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => 'data_start',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;
                    case 'data_end':
                        $massColums[$last]['value'] = function ($data){ 
                            return $data['data_end'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => 'data_end',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;  
                }
            }
            $massColums[] = [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ];
            $gridView['filterModel'] = $searchModel;
         //   debugArray($massColums);
        } elseif ($role == 'master') {
            $massColums = [
                ['class' => 'yii\grid\SerialColumn'],
                'id',              
                [
                    'attribute' => 'name', 
                    'label' => 'Название',
                    'contentOptions' => ['style'=>'white-space: normal;']
                ],                
                'data_registry',           
                'data_end',
                'cena',
                [                  
                    'attribute' => 'navik_name',                   
                    'label' => 'Требуемый навык',
                    'contentOptions' => ['style'=>'white-space: normal;']
                ], 
                'reyting_start',                
                [                  
                    'attribute' => 'gorod',                   
                    'label' => 'Город',
                    'contentOptions' => ['style'=>'white-space: normal;']
                ], 
                [                  
                    'attribute' => 'poselok',                   
                    'label' => 'Поселок',
                    'contentOptions' => ['style'=>'white-space: normal;']
                ],
                'ulica',   
                [              
                    'attribute' => 'status_zakaz_name',          
                    'label' => 'Статус заявки',
             //       'filter' => ArrayHelper::map($massFilters['vidStatusZakaz'], 'name', 'name'),
                    'contentOptions' => ['style'=>'white-space: normal;']
                ],           
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                ],                
            ];
        } 
        $gridView['columns'] = $massColums;
    ?>
    
    <?= GridView::widget($gridView); ?>
    <?php Pjax::end(); ?>
    
    <?php 
        if ($id = Yii::$app->session->get('aktivateZakaz')) {
         //   $region = VidRegion::find()->select(['name'])->where(['id' => Yii::$app->session->get('id_region')])->limit(1)->scalar();
            $this->registerJsVar('id', (int)$id);
           
            $this->registerJsFile('@web/js/system.js', ['depends' => 'frontend\assets\System']);
            Yii::$app->session->remove('aktivateZakaz');            
        }                
    ?>
    <div>
        <?php 
            if ($fields != null) {
                echo ShowFields::widget (['fields' => $fields]); 
                $this->registerJsFile('@web/js/select_fields.js', [
                    'dependst' => 'yii\web\YiiAsset',
                    'position' => View::POS_END]);   
            }
        ?>
    </div>
    
</div>
