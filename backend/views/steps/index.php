<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Html;

$this->title = 'Шаги';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="steps-index">    
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Добавить новый шаг', ['create'], ['class' => 'btn btn-success']) ?>
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
                    'label' => 'Сортировка',
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
                                                'confirm' => 'Удаление шага. '
                                                . 'Возможна потеря отображения заявок, продолжить?',
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

