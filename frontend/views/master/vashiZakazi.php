<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model common\models\Zakazi */

//debugArray($model);
$this->title = 'Ваши заявки';
$this->params['breadcrumbs'][] = ['label' => 'Кабинет мастера', 'url' => ['master/kabinet']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
//debugArray(count($model));
//debugArray(count($takeOrders));

    if (count($takeOrders) == 0 && count($model) == 0){ 
        echo '<div style="width : 300px; padding : 10px; margin-left : auto; '
            . 'margin-right : auto">' . 
                Html::label('За вами не закреплено ни одной заявки', null, 
                ['class' => 'btn btn-block btn-warning']) . '</div>'; 
    }

    if (count($model) > 0) {
        foreach ($model as $zakaz){
            echo '<div style="width : 300px; padding : 10px; margin-left : auto; '
            . 'margin-right : auto">' . Html::a('Заявка №' . $zakaz['id'], 
                    ['/zakaz/view', 'id' => $zakaz['id']], 
                    ['class' => 'btn btn-block btn-warning']) . '</div>';
            echo DetailView::widget([
                'model' => $zakaz,
                'attributes' => [                    
                    [
                        'label' => 'Изделие',
                        'value' => $zakaz['vidWork']['name']
                    ],
                    [
                        'label' => 'Необходимый навык',
                        'value' => $zakaz['navik']['name']
                    ],
                    [
                        'label' => 'Название',
                        'value' => $zakaz['name']
                    ],
                    [
                        'label' => 'Цена',
                        'value' => $zakaz['cena']
                    ],
                    [
                        'label' => 'Описание',
                        'value' => $zakaz['opisanie']
                    ],
                    [
                        'label' => 'Необходимый рейтинг',
                        'value' => $zakaz['reyting_start']
                    ],                
                    [
                        'label' => 'Город',
                        'value' => $zakaz['gorod']
                    ],
                    [
                        'label' => 'Поселок',
                        'value' => $zakaz['poselok']
                    ],
                    [
                        'label' => 'Улица',
                        'value' => $zakaz['ulica']
                    ],
                    [
                        'label' => 'Дом',
                        'value' => $zakaz['dom']
                    ],
                    [
                        'label' => 'Квартира',
                        'value' => $zakaz['kvartira']
                    ],
                    [
                        'label' => 'Статус заявки',
                        'value' => $zakaz['statusZakaz']['name']
                    ],
                    [
                        'label' => 'Шаг выполнения',
                        'value' => $zakaz['shag']['name']
                    ],                
                    [
                        'label' => 'Дата выдода на объект',
                        'value' => $zakaz['data_start']
                    ],
                    [
                        'label' => 'Дата завершения работ',
                        'value' => $zakaz['data_end']
                    ],                
                    [
                        'label' => 'Изображение',
                        'value' => $zakaz['image']
                    ],
                    [
                        'label' => 'Регион',
                        'value' => $zakaz['region']['name']
                    ],
                  /*  [
                        'label' => 'Оценка',
                        'value' => $zakaz['ocenka']
                    ],*/
                    [
                        'label' => 'Имя заказчика',
                        'value' => $zakaz['klient']['imya']
                    ],
                    [
                        'label' => 'Отчество заказчика',
                        'value' => $zakaz['klient']['otchestvo']
                    ],
                    [
                        'label' => 'Телефон заказчика',
                        'value' => $zakaz['klient']['phone']
                    ]
                 /*   [
                        'label' => 'Отзыв',
                        'value' => $zakaz['otziv']
                    ]*/
                ]
            ]); 
            echo '<div style="width : 300px; padding : 10px; margin-left : auto; '
            . 'margin-right : auto">' . Html::a(
                    'Отказаться от заявки №' . Html::encode($zakaz['id']), 
                    Yii::$app->urlManager->createUrl('/zakaz/init-diactivate-zakaz'), 
                    [
                        'data' => [
                            'method' => 'post',
                            'params' => [
                                'id' => $zakaz['id']
                            ]
                        ],
                        'class' => 'btn btn-block btn-danger',
                    ]) . '</div>';
        }
    } 
        
    if (count($takeOrders) > 0) {        
        echo '<div style="padding : 10px; margin-left : auto; '
                . 'margin-right : auto">' . Html::label('Взятые вами заявки ожидающие подтверждения менеджера', 
                        null, ['class' => 'btn btn-block btn-success']) . '</div>';
        
        foreach ($takeOrders as $zakaz){
            echo DetailView::widget([
                'model' => $zakaz,                
                'attributes' => [  
                    ['label' => 'Заявка №', 'value' => $zakaz['id']],
                    ['label' => 'Изделие', 'value' => $zakaz['vidWork']['name']],
                    ['label' => 'Необходимый навык', 'value' => $zakaz['navik']['name']],
                    ['label' => 'Название', 'value' => $zakaz['name']],
                    ['label' => 'Цена', 'value' => $zakaz['cena']],
                    ['label' => 'Описание', 'value' => $zakaz['opisanie']],
                    ['label' => 'Необходимый рейтинг', 'value' => $zakaz['reyting_start']],                
                    ['label' => 'Город', 'value' => $zakaz['gorod']],
                    ['label' => 'Поселок', 'value' => $zakaz['poselok']],              
                    ['label' => 'Статус заявки', 'value' => $zakaz['statusZakaz']['name']],                        
                    ['label' => 'Дата выдода на объект', 'value' => $zakaz['data_start']],
                    ['label' => 'Дата завершения работ','value' => $zakaz['data_end']],  
                    ['label' => 'Регион', 'value' => $zakaz['region']['name']],                 
                ]
            ]); 
        }
    }
?>
