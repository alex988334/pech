<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Html;

$this->title = 'Навыки';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="work-index">    
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Создать новый навык', ['create'], ['class' => 'btn btn-success']) ?>
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
                    'attribute' => 'sort',                   
                    'label' => 'Вес навыка (сортировка)',
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
                                                'confirm' => 'Удаление навыка. '
                                                . 'Возможна потеря отображения заявок и мастеров с таким навыком, продолжить?',
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

