<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\AuthItem;
use yii\helpers\ArrayHelper;
use common\widgets\ShowFields;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MasterWorkNavikSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Навыки мастеров';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-work-navik-index">
    
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?php 
        $colums = [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [       
                'attribute' => 'id_master',
                'format' => 'html',
                'label' => '№ мастера',
                'value' => function($data){
                    return Html::a($data['id_master'], 
                            Yii::$app->urlManager->createUrl(['/master/index', 'id_master' => $data['id_master']]));                    
                }            
            ], 
            ['attribute' => 'imya', 'label' => 'Имя'],
            ['attribute' => 'familiya', 'label' => 'Фамилия'],
            [
                'attribute' => 'work_name', 
                'label' => 'Вид работ',
                'filter' => ArrayHelper::map($massFilters['vidWork'], 'name', 'name'),
            ],
            [
                'attribute' => 'navik_name', 
                'label' => 'Навык',
                'filter' => ArrayHelper::map($massFilters['vidNavik'], 'name', 'name'),
            ],            
            [
                'attribute' => 'navik_sort', 
                'label' => 'Вес навыка',
                'filter' => ArrayHelper::map($massFilters['vidNavik'], 'sort', 'sort'),
            ]            
        ];
        if (!$role = Yii::$app->session->get('role')) {
            Yii::$app->user->logout();
        } elseif ($role == AuthItem::MASTER) {
            echo GridView::widget([
                'dataProvider' => $dataProvider,                
                'columns' => $colums
            ]);
            
        } elseif ($role == AuthItem::HEAD_MANAGER || $role == AuthItem::MANAGER) {
            echo '<p>', Html::a('Создание нового навыка', ['create'], ['class' => 'btn btn-success']), '</p>';
            $colums[] = ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}'];
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $colums
            ]);
        } else {
            Yii::$app->user->logout();
        }     
    ?>    
    
    <?php Pjax::end(); ?>
</div>
