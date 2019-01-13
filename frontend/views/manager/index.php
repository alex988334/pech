<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ManagerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Менеджеры';
$this->params['breadcrumbs'][] = $this->title;

//debugArray()
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
        if (count($mass) > 0) {
            foreach ($mass as $key => $one) {
                echo '<br><a href="#'. $key .'" data-toggle="collapse" class="btn btn-danger" style="margin: 3px; max-width: 500px;">НАРУШЕНИЕ ЦЕЛОСТНОСТИ БАЗЫ ДАННЫХ!!!</a>';
                
                echo '<div class="collapse" id="'. $key .'" style="border-radius: 4px; width: 1100px; background-color: #f6c4c4;">';
                
                echo '<div style="border-radius: 4px; width: 1100px; padding: 5px; background-color: #d43f3a; color: white;"><b>'. $one['error'] .'</b></div>';
                
                echo '<table border="2" style="table-layout: fixed; margin-top:10px; margin-bottom: 10px; margin-left:auto; margin-right: auto; width: 1000px; border-radius: 4px; border: solid grey 2px; ">';
                
                echo '<tr>';
                foreach ($one['lables'] as $label) {                    
                    echo '<td style="text-align: center; padding: 4px;">' .  $label . '</td>';                    
                }
                echo '</tr>';
                
                foreach ($one['values'] as $row){
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo '<td style="text-align: center; padding: 4px;">' .  $value . '</td>';      
                    }
                    echo '</tr>';
                }                
                echo '</table></div>';
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
