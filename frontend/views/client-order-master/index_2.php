<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\AuthItem;
use common\widgets\ShowFields;
use yii\web\View;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ClientOrderMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Client Order Masters';
$this->params['breadcrumbs'][] = $this->title;

echo 'date = '. date('U') . '<br>';
echo 'id = ' . Yii::$app->user->getId(). '<br>';
//debugArray($fields);
?>
<div class="client-order-master-index">

    <!--h1><?php // Html::encode($this->title) ?></h1-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новая связка клиент-заявка-мастер', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php 
      /*  $role = Yii::$app->session->get('role');
        $gridView = [  ];  
        if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
       //    debugArray($tablesId);
        //    debugArray($massFilters);
            $massColums = [
                ['class' => 'yii\grid\SerialColumn'],    
            ];        
            foreach ($fields as $one){
                if ($one['visibility_field'] == "0") continue;
                if ($one['name'] == 'id_status_on_off') continue;
             //   if ($tablesId[$one['clone_by']]['connection'] != 'order' && $one['name'] == 'id') continue;
                
                $massColums[] = [
                    'attribute' => $tablesId[$one['clone_by']]['connection'] .'.'. $one['name'],                   
                    'label' => $one['alias'],                 
                    'contentOptions' => ['style'=>'white-space: normal;'], 
                //    'filter' = ArrayHelper::map($massFilters['vidStatusWork'], 'id', 'name');
                    'visible' => true,
                    'value' => function ($data) use ($tablesId, $one){
                        return $data[$tablesId[$one['clone_by']]['connection']][$one['name']];
                    },                    
                    'filter' => Html::activeInput('text', $searchModel, $tablesId[$one['clone_by']]['connection']. '_'. $one['name'],
                            ['class' => 'form-control']),
                            
                ];
                $last = count($massColums) - 1;  
                
                switch ($one['name']){ 
                 /*   case 'id':
                        $massColums[$last]['format'] = 'raw';
                        $massColums[$last]['value'] = function ($data) {
                            return Html::a($data['id'], ['/master-vs-zakaz/index'], 
                                    [
                                        'title' => 'переход в мастера и заявки',
                                        'data' => [
                                            'method' => 'get', 
                                            'params' => [
                                                'id_zakaz' => $data['id'],
                                            ]
                                        ]
                                    ]);                        
                        };
                        break;             
          //       
                                   
                    case 'id_status_work': 
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_status_work';                   
                        $massColums[$last]['label'] = 'Статус работника';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){                       
                            return $massFilters['vidStatusWork'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_work']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusWork'], 'id', 'name');
                        break;
                    case 'id_vid_work': 
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_vid_work';                   
                        $massColums[$last]['label'] = 'Вид работ';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){                       
                    //     debugArray($massFilters);
                  //      debugArray($tablesId);
                 //        debugArray($data);
                  //       debugArray($one);
                            return $massFilters['vidWork'][$data[$tablesId[$one['clone_by']]['connection']]['id_vid_work']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidWork'], 'id', 'name');
                        break;
                    case 'id_navik':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_navik';                   
                        $massColums[$last]['label'] = 'Требуемый навык';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidNavik'][$data[$tablesId[$one['clone_by']]['connection']]['id_navik']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidNavik'], 'id', 'name');
                        break;
                    case 'id_status_zakaz':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_status_zakaz';                   
                        $massColums[$last]['label'] = 'Статус';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidStatusZakaz'][$data[$tablesId[$one['clone_by']]['connection']]['id_status_zakaz']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusZakaz'], 'id', 'name');
                        break;
                    case 'id_shag':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_shag';                   
                        $massColums[$last]['label'] = 'Шаг';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidShag'][$data[$tablesId[$one['clone_by']]['connection']]['id_shag']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidShag'], 'id', 'name');
                        break;
                    case 'id_region':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_region';                   
                        $massColums[$last]['label'] = 'Регион';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidRegion'][$data[$tablesId[$one['clone_by']]['connection']]['id_region']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidRegion'], 'id', 'name');
                        break;
                    case 'id_ocenka':
                        $massColums[$last]['attribute'] = $tablesId[$one['clone_by']]['connection'] . '_id_ocenka';                   
                        $massColums[$last]['label'] = 'Оценка';
                        $massColums[$last]['value'] = function ($data) use ($massFilters, $tablesId, $one){ 
                            return $massFilters['vidOcenka'][$data[$tablesId[$one['clone_by']]['connection']]['id_ocenka']]['name'];
                        };
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidOcenka'], 'id', 'name');
                        break;                                     
                }                
            }
        //    Yii::debug($massColums);
            $massColums[] = [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
            ];
            $gridView['filterModel'] = $searchModel;
        }
        
        $gridView['columns'] = $massColums;
    */
    
    ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
      /*      [
                'attribute' => 'id',
                'value' => function ($data){
                    debugArray($data);
                    return '';
                }
            ],
       //     'id_order',*/
                    
            'client.id_klient',
            'client.familiya',
            'client.otchestavo', 
            'client.reyting', 
            'client.balans',
                    
       
            'order.id_vid_work' ,     
            'order.id_navik',
            'order.cena',
            'order.reyting_start',
            'order.dom',
            'order.kvartira',
            'order.id_status_zakaz',
            'order.id_shag',
            'order.dolgota',   
            'order.shirota',   
            'order.dolgota_change',   
            'order.shirota_change',   
            'order.id_region',  
            'order.id_ocenka',
                    
            'master.id_master',     
            'master.master_vozrast',
            'master.staj',
            'master.reyting',
            'master.id_status_work',
            'master.balans',
            'master.id_region',
            'master.limit_zakaz'
        ]
    ]); ?>
    
    <?php /* GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

           /* 'id',
            'id_client',
            'id_order',
            [
                'attribute' => 'order.opisanie',                   
                'label' => 'Регион',
           //     'filter' => ArrayHelper::map($massFilters['vidRegion'], 'name', 'name'),
         //       'contentOptions' => ['style'=>'white-space: normal;']
            ],
          /*  [       
                'attribute' => 'id_klient',
                'format' => 'html',
                'label' => '№ клиента',
                'value' => function($data){
                    return Html::a($data['id_klient'], 
                            Yii::$app->urlManager->createUrl(['/klient/index', 'id_klient' => $data['id_klient']]),
                            ['title' => 'переход к клиентам']);                    
                }            
            ], 
            'id_master',
            'created_at',
            //'id_region',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); */ ?>
    <?php Pjax::end(); ?>
    
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
