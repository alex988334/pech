<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model common\models\Master */


$this->title = 'Кабинет мастера';
$this->params['breadcrumbs'][] = $this->title;
/*debugArray(Yii::$app->session->get('id_region'));
/*
debugArray(Yii::$app->session->get('region'));
debugArray(Yii::$app->session->get('role'));*/
//debugArray($model);
//   */
?>

<?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'Логин',
                'format' => 'html',
                'value' => $model['user']['username'] . Html::a('Сменить логин, пароль', 
                        '/site/change-login-password', ['class' => 'btn btn-warning', 'style' => 'margin-left: 10px;']),
            ],
            [
                'label' => 'Фамилия',
                'value' => $model['familiya'],
            ],
            [
                'label' => 'Имя',
                'value' => $model['imya'],
            ],
            [
                'label' => 'Отчество',
                'value' => $model['otchestvo'],
            ],
            [
                'label' => 'Статус подключения',
                'value' => $model['statusOnOff']['name']
            ],
            [
                'label' => 'Возраст',
                'value' => $model['vozrast'],
            ],
            [
                'label' => 'Стаж',
                'value' => $model['staj'],
            ],
            [
                'label' => 'Рейтинг',
                'value' => $model['reyting'],
            ],
            [
                'label' => 'Статус мастера',
                'value' => $model['statusWork']['name']
            ],
            [
                'label' => 'Дата регистрации',
                'value' => $model['data_registry'],
            ],
            [
                'label' => 'Дата увольнения',
                'value' => $model['data_unregistry'],
            ],
            [
                'label' => 'Телефон',
                'value' => $model['phone'],
            ],
            [
                'label' => 'Место жительства',
                'value' => $model['mesto_jitelstva'],
            ],
            [
                'label' => 'Место работы',
                'value' => $model['mesto_raboti'],
            ],
            [
                'label' => 'Баланс',
                'value' => $model['balans'],
            ],              
            [
                'label' => 'Регион',
                'value' => $model['region']['name']
            ],             
            [
                'label' => 'Лимит одновременно выполняемых заявок',
                'value' => $model['limit_zakaz']
            ]            
        ]
    ]); 
?>
<?php  
    if (count($model['masterVsZakaz']) > 0){
        $hrefName = 'Перейти к вашим заявкам: ';
        foreach ($model['masterVsZakaz'] as $one){
            $hrefName .= $one['id_zakaz'] . ', ';
        }         
        echo Html::a($hrefName, 
                Yii::$app->urlManager->createUrl(['/master/vashi-zakazi']), 
                ['class' => 'btn btn-warning btn-block']);
    }

  /* if (Yii::$app->session->get('zakazi')){
        
        foreach (Yii::$app->session->get('zakazi') as $zayavka){
            $hrefName .= $zayavka/*['id_zakaza'] . ', ';
        }
        
    }*/
?>
