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

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    
    <?php Pjax::begin(); ?>    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'username',
            'password_hash',
            'imei',
            [
                'class' => 'yii\grid\ActionColumn',
                'buttons'=>[                    
                    'reset-password' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-remove-sign"></span>', 
                            ['/site/reset'], 
                            [
                                'title' => 'Сброс пароля мастера',
                                'data' => [
                                    'method' => 'post',  
                                    'confirm' => 'Сброс пароля пользователя на стандартный пароль "PechnoyMir", продолжить?',
                                    'params' => [
                                        'type' => 2,
                                        'id' => $model['id'],                                  
                                    ]
                                ]
                            ]);                        
                    },
                    'reset-imei' => function ($url, $model) {  
                        return Html::a('<span class="glyphicon glyphicon-phone"></span>', 
                            ['/site/reset'], 
                            [
                                'title' => 'Сброс IMEI',
                                'data' => [
                                    'method' => 'post',  
                                    'confirm' => 'Сброс IMEI пользователя (для мобильного приложения), продолжить?',
                                    'params' => [
                                        'type' => 3,
                                        'id' => $model['id'],                                  
                                    ]
                                ]
                            ]);                        
                    }
                ],
                'template' => '{reset-password} {reset-imei}',
            ]
        ]
    ]);
    ?>   
    <?php Pjax::end(); ?>    
</div>