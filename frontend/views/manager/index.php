<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ManagerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Менеджеры';
$this->params['breadcrumbs'][] = $this->title;
//debugArray([date('d-m-Y', 1540361486)]);
//debugArray($massErrorsDB);
?>
<div class="manager-index">
   
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php // Html::a('Create Manager', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
     //   'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            'id',
            'id_manager',
            [
                'value' => 'user.username',
                'label' => 'Логин'
            ],
            'familiya',
            'imya',
            'otchestvo',            
            [
                'value' => 'region.name',
                'label' => 'Регион'
            ],
            'phone1',
            'phone2',
            'phone3',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}' // {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end();  ?>
    
    <?php 
        if (count($massErrorsDB) > 0) {
            foreach ($massErrorsDB as $key => $one) {
                if (key_exists('created_at', $one['values'][0])) {
                    for ($i=0; $i<count($one['values']); $i++) {
                        $massErrorsDB[$key]['values'][$i]['created_at'] = date('d-m-Y', $massErrorsDB[$key]['values'][$i]['created_at']);
                    }                    
                } 
                if (key_exists('updated_at', $one['values'][0])) {
                    for ($i=0; $i<count($one['values']); $i++) {
                        $massErrorsDB[$key]['values'][$i]['updated_at'] = date('d-m-Y', $massErrorsDB[$key]['values'][$i]['updated_at']);
                    }
                } 
            }
            echo '<br>';
            foreach ($massErrorsDB as $key => $one) {
                echo '<div class="container-error">';
                echo '<a href="#'. $key .'" data-toggle="collapse" class="btn btn-danger" style="margin: 3px; max-width: 500px;">НАРУШЕНИЕ ЦЕЛОСТНОСТИ БАЗЫ ДАННЫХ!!!</a>';
                
                echo '<div class="collapse" id="'. $key .'" style="border-radius: 4px; width: 1100px;">';
                
                echo '<div class="table-error_head"><b>'. $one['error'] .'</b></div>';
                
                echo '<table border="2" class="table-error">';
                
                echo '<tr>';
                foreach ($one['lables'] as $label) {                    
                    echo '<td class="table-error_td"><b>' .  $label . '</b></td>';                    
                }
                echo '</tr>';
                
                foreach ($one['values'] as $row){
                    echo '<tr>';
                    foreach ($row as $k => $value) {  
                        if (count($one['url']) && key_exists($k, $one['url'])) {
                            $v = Html::a($value, $one['url'][$k], ['data' => ['method' => 'post', 'params' => [$k => $value]]]);
                        } else {
                            $v = $value;
                        }
                        echo '<td class="table-error_td">' . $v . '</td>';                       
                    }
                    echo '</tr>';
                }                
                echo '</table></div></div>';
            }
            
            /*GridView::widget([
                'dataProvider' => $massZakaz,            
                'columns' => [
                   // ['class' => 'yii\grid\SerialColumn'],

                    'id', 'work_name', 'name', 'region_name',

                    
                    
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => ''
                    ],
                ],
            ]); */
        }
    ?>
</div>
