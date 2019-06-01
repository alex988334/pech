<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\models\KlientVsZakazSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Связи клиентов и заявок';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="klient-vs-zakaz-index">
    
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Новая связка клиент-заявка', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

        //    'id',
            [                  
                'attribute' => 'region_name',                   
                'label' => 'Регион',
                'filter' => ArrayHelper::map($massFilters['vidRegion'], 'name', 'name'),
                'contentOptions' => ['style'=>'white-space: normal;']
            ],    
            [       
                'attribute' => 'id_klient',
                'format' => 'html',
                'label' => '№ клиента',
                'value' => function($data){
                    return Html::a($data['id_klient'], 
                            Yii::$app->urlManager->createUrl(['/klient/index', 'id_klient' => $data['id_klient']]),
                            ['title' => 'переход к клиентам']);                    
                }            
            ],             
            [                  
                'attribute' => 'username',                   
                'label' => 'Логин',
                'contentOptions' => ['style'=>'white-space: normal;']
            ],            
            [                  
                'attribute' => 'imya',                   
                'label' => 'Имя',
                'contentOptions' => ['style'=>'white-space: normal;']
            ],            
            [                  
                'attribute' => 'familiya',                   
                'label' => 'Фамилия',
                'contentOptions' => ['style'=>'white-space: normal;']
            ],
            [       
                'attribute' => 'id_zakaz',
                'format' => 'html',
                'label' => '№ заявки',
                'value' => function($data){
                    return Html::a($data['id_zakaz'], 
                            Yii::$app->urlManager->createUrl(['/zakaz/index', 'id' => $data['id_zakaz']]),
                            ['title' => 'переход к заявкам']);                    
                }            
            ],                         
            [                  
                'attribute' => 'name',                   
                'label' => 'Название',
                'contentOptions' => ['style'=>'white-space: normal;']
            ],
            [                  
                'attribute' => 'opisanie',                   
                'label' => 'Описание',
                'contentOptions' => ['style'=>'white-space: normal;']
            ],
            [                  
                'attribute' => 'vid_work_name',                   
                'label' => 'Вид работ',
                'filter' => ArrayHelper::map($massFilters['vidWork'], 'name', 'name'),
                'contentOptions' => ['style'=>'white-space: normal;']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'master-zakaz' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-share-alt"></span>', 
                            ['/master-vs-zakaz/index'], 
                            [
                                'title' => 'Мастера и заявки',
                                'data' => [
                                    'method' => 'get',                                    
                                    'params' => [
                                        'id_zakaz'=> $model['id_zakaz'],                                    
                                    ]
                                ]
                            ]);                        
                    }
                ],
                'template' => '{master-zakaz} {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
