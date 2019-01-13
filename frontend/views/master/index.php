<?php

use yii\web\View;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use common\models\AuthItem;
use common\widgets\ShowFields;
/* @var $this yii\web\View */
/* @var $searchModel common\models\MasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Мастера';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-index">
    
    <?php Pjax::begin(); ?>
    <p>
        <?= $r = (Yii::$app->session->get('role') == 'manager') 
            ? Html::a('Добавить нового мастера', ['create'], 
                    ['class' => 'btn btn-success']) : ''; ?>
    </p>
    <?php    
        $gridView = [ 'dataProvider' => $dataProvider ];  
      //  if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
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
                    case 'id_status_on_off': 
                        $massColums[$last]['attribute'] = 'status_on_off_name';                   
                        $massColums[$last]['label'] = 'Статус подключения';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusOnOff'], 'name', 'name');
                        break;
                    case 'id_status_work':
                        $massColums[$last]['attribute'] = 'status_work_name';                   
                        $massColums[$last]['label'] = 'Статус работника';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusWork'], 'name', 'name');
                        break;                    
                    case 'id_region':
                        $massColums[$last]['attribute'] = 'region_name';                   
                        $massColums[$last]['label'] = 'Регион';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidRegion'], 'name', 'name');
                        break;                                                      
                }
            } 
            $massColums[] = [
                'class' => 'yii\grid\ActionColumn',
                'buttons'=>[
                    'navik' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-wrench"></span>', 
                            ['/navik/index'], 
                            [
                                'title' => 'Навыки',
                                'data' => [
                                    'method' => 'get',                               
                                    'params' => [
                                        'id_master'=> $model['id_master'],                                    
                                    ]
                                ]
                            ]);                        
                    },
                    'zakaz' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-paperclip"></span>', 
                            ['/master-vs-zakaz/index'], 
                            [
                                'title' => 'Прикрепленные заявки',
                                'data' => [
                                    'method' => 'get',                                
                                    'params' => [
                                        'id_master'=> $model['id_master'],                                  
                                    ]
                                ]
                            ]);                        
                    },
                    'reset-password' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-paperclip"></span>', 
                            ['/master/reset-password'], 
                            [
                                'title' => 'Сброс пароля мастера',
                                'data' => [
                                    'method' => 'post',  
                                    'confirm' => 'Сброс пароля пользователя на стандартный пароль "PechnoyMir", продолжить?',
                                    'params' => [
                                        'id'=> $model['id_master'],                                  
                                    ]
                                ]
                            ]);                        
                    },
                ],
                'template' => '{view} {update} {delete} {navik} {zakaz} {reset-password}',
            ];        
            $gridView['filterModel'] = $searchModel;  
            $gridView['columns'] = $massColums;
     //   }
    ?>
    
    <?= GridView::widget($gridView); ?>   
    <?php Pjax::end(); ?>
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