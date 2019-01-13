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
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= $r = ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) 
            ? Html::a('Добавить новую заявку', ['create'], 
                    ['class' => 'btn btn-success']) : ''; ?>
    </p>
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
            $js = ' var data = {"action" : "system", "id" : ' . $id 
                    .', "status" : 150 }; if (securityWebSocket()) { console.log("ОТПРАВКА");'
                    . ' chat.send(JSON.stringify(data)); } else '
                    . '{ massMessages.push(data); console.log("Ожидание отправки"); console.log(massMessages);}';
           // if (securityWebSocket()){}
            /*$js = '(new WebSocket("ws://expertpech.ru:25555")).onopen = function(e) {
                    this.send( JSON.stringify({"action" : "setRegion", "region" : "' . $region . '", "head" : true}) );
                    this.send( JSON.stringify({"action" : "chat", "message" : "Заявка №' . $id . ' закреплена за мастером №' . Yii::$app->user->getId() . '"}) );
                    this.close();
                    console.log("СОБЫТИЕ открытия сокета");
                    console.log("' . $region . '");
                };';*/
            Yii::$app->session->remove('aktivateZakaz');
            $this->registerJs($js, yii\web\View::POS_READY);            
        }                
    ?>
    <div>
        <?php 
            if ($fields != null) {
                echo ShowFields::widget (['fields' => $fields]); 
                $this->registerJsFile('@web/js/select_fields.js', [
                    'dependst' => 'yii\web\YiiAsset',
                    'position' => View::POS_END]);                
                $this->registerJs(
                    'massChange = []; $("#selectedFields").children("input").each(function (index, value){
                    this.onclick = function(elem) { 
                    var one = $(massChange).filter(function (i, k){ return k[0] == elem.target.id; });
                    if (one.length != 0) { if (elem.target.checked) one[0].visible = 1; else one[0].visible = 0; 
                    } else massChange.push({id: elem.target.id, visible: elem.target.checked});};});',
                    View::POS_READY
                );
            }
        ?>
    </div>
    
</div>