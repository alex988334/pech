<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use common\models\AuthItem;
use common\widgets\ShowFields;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $searchModel common\models\HistoryMasterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'History Masters';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-master-index">
    
    <?php Pjax::begin(); ?>    
    
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
                    case 'id_status_history': 
                        $massColums[$last]['attribute'] = 'status_history_name';                   
                        $massColums[$last]['label'] = 'Статус записи';
                        $massColums[$last]['filter'] = ArrayHelper::map($massFilters['vidStatusHistory'], 'name', 'name');
                        break;
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
            $gridView['filterModel'] = $searchModel;  
            $gridView['columns'] = $massColums;
        //}
        echo GridView::widget($gridView);
    ?>
   
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
