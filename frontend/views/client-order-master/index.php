<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\AuthItem;
use common\widgets\ShowFields;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ClientOrderMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Клиент-заявка-мастер';
$this->params['breadcrumbs'][] = $this->title;

//echo 'date = '. date('U') . '<br>';
//echo 'id = ' . Yii::$app->user->getId(). '<br>';
//debugArray($fields);
?>
<div class="client-order-master-index">

    <!--h1><?php // Html::encode($this->title) ?></h1-->
    <?php // Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php if (Yii::$app->session->get('invisibleExecutedOrders') == FALSE) {
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
        <?= Html::a('Новая связка клиент-заявка', ['create-client-order'], ['class' => 'btn btn-success']); ?>
        <?= Html::a($buttonLabel, ['/zakaz/show-executed-orders'], ['class' => $class]); ?>
        <?= Html::a($buttonLabel1, ['/zakaz/show-blocked-orders'], ['class' => $class1]); ?>
    </p>
    <?php 
        $role = Yii::$app->session->get('role');
        $gridView = [ 'dataProvider' => $dataProvider ];  
        if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
       //    debugArray($tablesId);
        //    debugArray($massFilters);
      //  debugArray($fields);
            $massColums = [
                ['class' => 'yii\grid\SerialColumn'],    
            ]; 
            
            if ($fields[4]['visibility_field'] == '1') {                        //  Дата создания связки created_at
                $massColums[] = [
                    'attribute' => $fields[4]['name'],                   
                    'label' => $fields[4]['alias'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'],                 
                    'visible' => true, 
                    'format' => ['date', 'php:d-m-Y'],
                 /*   $massColums[$last]['value'] = function ($data) use ($tablesId, $one){ 
                                return $data[$tablesId[$one['clone_by']]['connection']]['data_unregistry'] ?? NULL;
                            };*/
                    'filter' => yii\jui\DatePicker::widget([                
                        'model' => $searchModel,
                        'attribute' => 'created_at',
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ])                        
                ];
            }
            if ($fields[5]['visibility_field'] == '1') {                        //  № региона id_region
                $massColums[] = [
                    'attribute' => $fields[5]['name'],                   
                    'label' => $fields[5]['alias'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'],                 
                    'visible' => true,  
                    'value' => function ($data) use ($massFilters){ 
                        return $massFilters['vidRegion'][$data['id_region']]['name'] ?? ''; 
                    },
                    'filter' => ArrayHelper::map($massFilters['vidRegion'], 'id', 'name'),
                ];
            }
            if ($fields[1]['visibility_field'] == '1') {                        //  № клиента id_client
                $massColums[] = [
                    'attribute' => $fields[1]['name'],                  
                    'label' => $fields[1]['alias'], 
                    'contentOptions' => ['style'=>'white-space: normal;'],  
                    'format' => 'raw',
                    'value' => function ($data){
                        return Html::a($data['id_client'], ['/klient/index'], 
                                [ 'title' => 'переход к клиентам',
                                    'data' => ['method' => 'get', 'params' => [ 'id_klient' => $data['id_client']]]
                                ]) ?? '';                             
                    }
                ];
            }
            
            foreach ($fields as $one){
                
                if ($one['name'] == 'id' || $one['name'] == 'id_status_on_off' || $one['name'] == 'id_client' 
                        || $one['name'] == 'id_order' || $one['name'] == 'id_master' 
                        || $one['name'] == 'id_region' || $one['name'] == 'created_at') continue;
                      
                if ($tablesId[$one['clone_by']]['connection'] == 'order' && $one['name'] == 'id_vid_work') {
                    $massColums[] = [
                        'class' => 'yii\grid\ActionColumn',
                        'buttons' => [
                            'delete' => function ($url, $model) {  
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete'], 
                                        [ 'title' => 'Полное удаление',
                                            'data' => ['method' => 'post', 'params' => [ 'id' => $model['id']],
                                                'confirm' => 'Полное удаление связки "клиент-заявка-мастер".'
                                                . 'Параметры прикрепленного мастера откатятся к состоянию до взятия заявки, '
                                                . 'заявка останется без связи с клиетном!'
                                                . "\n" .'Продолжить?',
                                             ]
                                        ]); 
                            },
                        ],
                        'template' => '{delete}',
                    ];
                    if ($fields[2]['visibility_field'] == '1') {                // № заявки id_order
                        $massColums[] = [
                            'attribute' => $fields[2]['name'],                      
                            'label' => $fields[2]['alias'],                 
                            'contentOptions' => ['style'=>'white-space: normal;'],                 
                            'visible' => true,
                            'format' => 'raw',
                            'value' => function ($data){
                                return Html::a($data['id_order'], ['/zakaz/index'], 
                                        [ 'title' => 'Заявки',
                                            'data' => ['method' => 'get', 'params' => [ 'id' => $data['id_order']]]
                                        ]) ?? '';                             
                            }
                        ];
                    }
                    
                }
                if ($tablesId[$one['clone_by']]['connection'] == 'master' && $one['name'] == 'familiya') {
                    $massColums[] = [
                        'class' => 'yii\grid\ActionColumn',
                        'buttons' => [
                            'delete' => function ($url, $model) {  
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete-master'], 
                                        [ 'title' => 'Удаление мастера',
                                            'data' => ['confirm' => 'Открепить мастера от заявки?', 
                                                    'method' => 'post', 'params' => [ 'id' => $model['id']]]
                                        ]); 
                            },
                            'gotov' => function ($url, $model) {  
                                return Html::a('<span class="glyphicon glyphicon-ok"></span>', 
                                    ['/client-order-master/order-end'], 
                                    [
                                        'title' => 'Пометить как готовую',
                                        'data' => ['method' => 'post',
                                            'confirm' => 'Отметить заявку как завершенную?', 'params' => ['id' => $model['id']]
                                        ]
                                    ]);                        
                            },
                            'set-master' => function ($url, $model) {  
                                return Html::a('<span class="glyphicon glyphicon-paperclip"></span>', 
                                    ['/client-order-master/create-order-master'], 
                                    [ 'title' => 'Прикрепить мастера',
                                        'data' => ['method' => 'get', 'params' => ['id' => $model['id']]]
                                    ]);                        
                            },
                        ],
                        'template' => '{set-master} {gotov} {delete}',
                    ];
                    if ($fields[3]['visibility_field'] == '1') {                //  № мастера id_master
                        $massColums[] = [
                            'attribute' => $fields[3]['name'],                   
                            'label' => $fields[3]['alias'],                 
                            'contentOptions' => ['style'=>'white-space: normal;'],                 
                            'visible' => true,  
                            'format' => 'raw',
                            'value' => function ($data){
                                return Html::a($data['id_master'], ['/master/index'], 
                                        [ 'title' => 'Мастера',
                                            'data' => ['method' => 'get', 'params' => [ 'id_master' => $data['id_master']]]
                                        ]) ?? '';                             
                            }
                        ];
                    }
                }
                
                if ($one['visibility_field'] == "0") continue;  
                
              /*  if ($one['name'] == 'id_master'){
                    $massColums[] = [
                        'attribute' => 'client_order_master.id_master',                   
                        'label' => $one['alias'],                 
                        'contentOptions' => ['style'=>'white-space: normal;'],
                        'format' => 'raw',
                        'value' => function ($data){ 
                                return Html::a($data['id_master'], ['/master/index'], 
                                        [ 'title' => 'Переход к мастерам',
                                            'data' => [ 'method' => 'get', 
                                                    'params' => [ 'id_master' => $data['id_master'],
                                                ]
                                            ]
                                        ]) ?? ''; 
                        },
                        'filter' => Html::activeInput('text', $searchModel, $one['name'], ['class' => 'form-control']),   
                    ];
                    continue;
                }*/
                
                $massColums[] = [
                    'attribute' => $tablesId[$one['clone_by']]['connection'] .'.'. $one['name'],                   
                    'label' => $one['alias'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'],                 
                    'visible' => true,
                    'value' => function ($data) use ($tablesId, $one){
                        return $data[$tablesId[$one['clone_by']]['connection']][$one['name']] ?? '';
                    }, 
                    'filter' => Html::activeInput('text', $searchModel, $tablesId[$one['clone_by']]['connection']. '_'. $one['name'],
                            ['class' => 'form-control']), 
                ];
                $last = count($massColums) - 1;  
                
                switch ($one['name']){ 
                   // case 'data_registry':
                      /*/*  $massColums[$last]['format'] = 'raw';
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_data_registry'; 
                        $massColums[$last]['filter'] = DatePicker::widget(['language' => 'ru', 'dateFormat' => 'yyyy-MM-dd']);
                               // function ($data) {return Html::a($data['id'], ['/master-vs-zakaz/index'], ['title' => 'переход в мастера и заявки','data' => ['method' => 'get', 'params' => ['id_zakaz' => $data['id'],]]]); };
                       */
                  //          break;
                 
                    case 'phone':                             
                        $massColums[$last]['filter'] = false;                       
                        break;                  
                    case 'id_status_work': 
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_status_work';                   
                        $massColums[$last]['label'] = 'Статус работника';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidStatusWork'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_work']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusWork'], 'id', 'name');
                        break;
                    case 'id_vid_work': 
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_vid_work';                   
                        $massColums[$last]['label'] = 'Вид работ';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){                       
                    //     debugArray($massFilters);
                   //     debugArray($tablesId);
                    //     debugArray($data);
                    //     debugArray($one);
                            return $massFilters['vidWork'][$data[$tablesId[$one['clone_by']]['connection']]['id_vid_work']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidWork'], 'id', 'name');
                        break;
                    case 'id_navik':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_navik';                   
                        $massColums[$last]['label'] = 'Требуемый навык';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidNavik'][$data[$tablesId[$one['clone_by']]['connection']]['id_navik']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidNavik'], 'id', 'name');
                        break;
                    case 'id_status_zakaz':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_status_zakaz';                   
                        $massColums[$last]['label'] = 'Статус';
                        $massColums[$last]['format'] = 'raw';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            
                            switch ($data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']) {
                                case \common\models\VidStatusZakaz::ORDER_REQUEST_TAKE:                            
                                    return $massFilters['vidStatusZakaz'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']]['name']
                                            . '<br>' . Html::a('<span class="glyphicon glyphicon-ok"></span>', null,
                                            [
                                                'title' => 'Подтвердить запрос взятия',
                                                'onclick' => '$.ajax({type: "post", url: "'
                                                    . Yii::$app->urlManager->createUrl(['/zakaz/accept-take-order']) .'", dataType: "json", data: {"id": this.name},
                                                        success: function (data){console.log(data); if (data.status == STATUS_ACCEPT) window.location.reload();},
                                                    });',
                                                'name' => $data['id_order'],                                      
                                            ]) ?? ''; 
                                    break; 
                                case \common\models\VidStatusZakaz::ORDER_REQUEST_REJECTION:                            
                                    return $massFilters['vidStatusZakaz'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']]['name']
                                            . '<br>' . Html::a('<span class="glyphicon glyphicon-repeat"></span>', 
                                            null, 
                                            [
                                                'title' => 'Сбросить запрос отказа',
                                                'onclick' => '$.ajax({type: "post", url: "'
                                                    . Yii::$app->urlManager->createUrl(['/zakaz/repeat']) .'", dataType: "json", data: {"id": this.name},
                                                        success: function (data){console.log(data); if (data.status == STATUS_ACCEPT) window.location.reload();},
                                                    });',
                                                'name' => $data['id_order'],  
                                                /*'data' => [
                                                    'method' => 'post',
                                                    'confirm' => 'Сбросить запрос отказа? (мастер продолжит выполнение)',
                                                    'params' => [ 'id'=> $data['id_order'] ]
                                                ]*/
                                            ]) ?? '';
                                    break;                        
                                default : return $massFilters['vidStatusZakaz'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']]['name'] ?? '';
                            } 

                            return $massFilters['vidStatusZakaz'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusZakaz'], 'id', 'name');
                        break;
                    case 'id_shag':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_shag';                   
                        $massColums[$last]['label'] = 'Шаг';                        
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidShag'][$data[$tablesId[$one['clone_by']]['connection']]['id_shag']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidShag'], 'id', 'name');
                        break;
                  /*  case 'id_region':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_region';                   
                        $massColums[$last]['label'] = 'Регион';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidRegion'][$data[$tablesId[$one['clone_by']]['connection']]['id_region']]['name'] ?? ''; 
                          //      $massFilters['vidRegion'][$data[$tablesId[$one['clone_by']]['connection']]['id_region']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidRegion'], 'id', 'name');
                        break;*/
                    case 'id_ocenka':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_ocenka';                   
                        $massColums[$last]['label'] = 'Оценка';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidOcenka'][$data[$tablesId[$one['clone_by']]['connection']]['id_ocenka']]['name'] ?? '';
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidOcenka'], 'id', 'name');
                        break; 
                    case 'data_unregistry':
                        $massColums[$last]['value'] = function ($data) use ($tablesId, $one){ 
                            return $data[$tablesId[$one['clone_by']]['connection']]['data_unregistry'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => $tablesId[$one['clone_by']]['connection'] . '_data_unregistry',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;  
                    case 'data_registry':
                        $massColums[$last]['value'] = function ($data) use ($tablesId, $one){ 
                            return $data[$tablesId[$one['clone_by']]['connection']]['data_registry'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => $tablesId[$one['clone_by']]['connection'] . '_data_registry',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;
                    case 'data_start':
                        $massColums[$last]['value'] = function ($data) use ($tablesId, $one){ 
                            return $data[$tablesId[$one['clone_by']]['connection']]['data_start'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => $tablesId[$one['clone_by']]['connection'] . '_data_start',
                            'language' => 'ru',
                            'dateFormat' => 'dd-MM-yyyy',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ]);
                        $massColums[$last]['format'] = ['date', 'php:d-m-Y'];
                        break;
                    case 'data_end':
                        $massColums[$last]['value'] = function ($data) use ($tablesId, $one){ 
                            return $data[$tablesId[$one['clone_by']]['connection']]['data_end'] ?? NULL;
                        };
                        $massColums[$last]['filter'] = yii\jui\DatePicker::widget([                
                            'model' => $searchModel,
                            'attribute' => $tablesId[$one['clone_by']]['connection'] . '_data_end',
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
        //    Yii::debug($massColums);
            
            $gridView['filterModel'] = $searchModel;
        }
        
        $gridView['columns'] = $massColums;
    ?>
    
    <?= GridView::widget($gridView); ?>
    
    <?php //Pjax::end(); ?>
    
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
