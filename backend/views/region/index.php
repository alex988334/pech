<?php

use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\helpers\Html;

$this->title = 'Регионы';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="region-index">    
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Добавить новый регион', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php if (isset($dataProvider)){
        echo GridView::widget([
        'dataProvider' => $dataProvider,      
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],    
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',                   
                    'label' => '№',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'name',                   
                    'label' => 'Название',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'parent_id',                   
                    'label' => '№ родительского региона',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'dolgota',                   
                    'label' => 'Долгота',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'shirota',                   
                    'label' => 'Широта',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'delete' => function($url, $data){
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', 
                                        ['delete'], 
                                        [   
                                            'title' => 'Удаление',
                                            'data' => [
                                                'method' => 'post', 
                                                'params' => [ 'id' => $data->id],
                                                'confirm' => 'Удаление региона. Возможна потеря отображения заявок из этого региона, продолжить?',
                                            ]
                                        ]);            
                        }
                    ],
                    'template' => '{update} {delete}', 
                ]
        ],      
        'filterModel'=> $searchModel]);
    }
    ?>   
    
    <?php  Pjax::end(); ?>  
</div>

