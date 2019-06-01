<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use frontend\controllers\MasterController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MasterVsZakazSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Мастера и заявки';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="master-vs-zakaz-index">    
    <?php Pjax::begin(); ?>

    <p>
        <?= Html::a('Новая связка мастер-заявка', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
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
                'attribute' => 'id_master',                   
                'label' => '№ мастера',
                'format' => 'html',
                'value' => function($data){
                    return Html::a($data['id_master'], 
                            Yii::$app->urlManager->createUrl(['/master/index', 'id_master' => $data['id_master']]),
                            ['title' => 'переход к мастерам']);                    
                }, 
                'contentOptions' => ['style'=>'white-space: normal;']
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
                'label' => '№ заявки',
                'format' => 'html',
                'value' => function($data){
                    return Html::a($data['id_zakaz'], 
                            Yii::$app->urlManager->createUrl(['/zakaz/index', 'id' => $data['id_zakaz']]),
                            ['title' => 'переход к заявкам']);                    
                }, 
                'contentOptions' => ['style'=>'white-space: normal;']
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
                'attribute' => 'status_zakaz_name',                   
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function($data){
                    switch ($data['id_status_zakaz']) {
                        case \common\models\VidStatusZakaz::ORDER_REQUEST_TAKE:                            
                            return $data['status_zakaz_name'] . '<br>' . Html::a('<span class="glyphicon glyphicon-ok"></span>', null,
                                [
                                    'title' => 'Подтвердить запрос взятия',
                                    'onclick' => '$.ajax({type: "post", url: "'
                                        . Yii::$app->urlManager->createUrl(['/zakaz/accept-take-order']) .'", dataType: "json", data: {"id": this.name},
                                            success: function (data){console.log(data); if (data.status == STATUS_ACCEPT) window.location.reload();},
                                        });',
                                    'name' => $data['id_zakaz'],                                      
                                ]); 
                            break; 
                        case \common\models\VidStatusZakaz::ORDER_REQUEST_REJECTION:                            
                            return $data['status_zakaz_name'] . '<br>' . Html::a('<span class="glyphicon glyphicon-repeat"></span>', 
                                ['/master-vs-zakaz/repeat'], 
                                [
                                    'title' => 'Сбросить запрос отказа',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'Сбросить запрос отказа? (мастер продолжит выполнение)',
                                        'params' => [ 'id'=> $data['id'] ]
                                    ]
                                ]);
                            break;                        
                        default : return $data['status_zakaz_name'];
                    } 
                                                       
                }, 
                'filter' => ArrayHelper::map($massFilters['vidStatusZakaz'], 'name', 'name'),
                
           //     'contentOptions' => ['style'=>'white-space: normal;']
            ],
            [                  
                'attribute' => 'shag_name',                   
                'label' => 'Шаг выполнения',
                'filter' => ArrayHelper::map($massFilters['vidShag'], 'name', 'name'),
                'contentOptions' => ['style'=>'white-space: normal;']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons'=>[
                    'gotov' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-ok"></span>', 
                            ['/master-vs-zakaz/gotov'], 
                            [
                                'title' => 'Пометить как готовую',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Отметить заявку как завершенную?',
                                    'params' => [
                                        'id'=> $model['id'],                                    
                                    ]
                                ]
                            ]);                        
                    },
                   /* 'notreboot' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-repeat"></span>', 
                            ['/master-vs-zakaz/repeat'], 
                            [
                                'title' => 'Сбросить запрос отказа',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Сбросить запрос отказа? (мастер продолжит выполнение)',
                                    'params' => [
                                        'id'=> $model['id'],                                    
                                    ]
                                ]
                            ]);                        
                    },*/
                    'klient-zakaz' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-share-alt"></span>', 
                            ['/klient-vs-zakaz/index'], 
                            [
                                'title' => 'Клиенты и заявки',
                                'data' => [
                                    'method' => 'get',                                    
                                    'params' => [
                                        'id_zakaz'=> $model['id_zakaz'],                                    
                                    ]
                                ]
                            ]);                        
                    }
                ],               
                'template' => '{klient-zakaz} {gotov} {delete}', //  {notreboot} 
            ],
        ],
    ]); 
    if (!empty($status) && $status == MasterController::STATUS_ACCEPT) {
        echo '$status = ' . $status;
    }    
    
    
?>
    <?php Pjax::end(); ?>
</div>
