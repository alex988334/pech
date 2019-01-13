<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Zakazi;

/* @var $this yii\web\View */
/* @var $model common\models\Zakazi */
$role = Yii::$app->session->get('role');

$this->title = 'Заявка №' . $model['id'];
if ($role == 'master') $this->params['breadcrumbs'][] = ['label' => 'Заявки по группам', 'url' => ['/zakaz/vid']];
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 
    'url' => Yii::$app->urlManager->createUrl(['/zakaz/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zakazi-view">      
    <p>        
        <?php 
    //    debugArray($model);            
            if ($role == 'manager' || $role == 'head_manager'){
                echo '<div style="margin-right: 20px; display: inline-block;">' 
                . Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) . '</div>'; 
                echo '<div style="margin-right: 20px; display: inline-block;">' 
                . Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Подтвердите удаление заявки',
                        'method' => 'post',
                    ],
                ]) . '</div>'; 
                $attributes = [
                    ['attribute' => 'id', 'label' => '№'],
                    ['attribute' => 'vidWork.name', 'label' => 'Вид изделия'], 
                    ['attribute' => 'navik.name', 'label' => 'Необходимый навык'],
                    ['attribute' => 'name', 'label' => 'Название'],
                    ['attribute' => 'cena', 'label' => 'Цена'],
                    ['attribute' => 'opisanie', 'label' => 'Описание'],
                    ['attribute' => 'zametka', 'label' => 'Заметка'],
                    ['attribute' => 'reyting_start', 'label' => 'Необходимый рейтиг'],
                    ['attribute' => 'gorod', 'label' => 'Город'],
                    ['attribute' => 'poselok', 'label' => 'Поселок'],
                    ['attribute' => 'ulica', 'label' => 'Улица'],
                    ['attribute' => 'dom', 'label' => 'Дом'],
                    ['attribute' => 'kvartira', 'label' => 'Квартира'],
                    ['attribute' => 'statusZakaz.name', 'label' => 'Статус заявки'],
                    ['attribute' => 'shag.name', 'label' => 'Шаг выполнения'],
                    ['attribute' => 'data_registry', 'label' => 'Дата регистрации'],
                    ['attribute' => 'data_start', 'label' => 'Дата выхода на объект'],
                    ['attribute' => 'data_end', 'label' => 'Дата завершения'],
                    ['attribute' => 'dolgota', 'label' => 'Долгота'],
                    ['attribute' => 'shirota', 'label' => 'Широта'],
                    ['attribute' => 'dolgota_change', 'label' => 'Долгота искаженная'],
                    ['attribute' => 'shirota_change', 'label' => 'Широта искаженная'],
                    [
                        'format' => 'html',
                        'value' => Html::img('/uploads/image/' . $model->image), 
                        'label' => 'Изображение'
                    ],         
                    ['attribute' => 'region.name', 'label' => 'Регион'],
                    ['attribute' => 'ocenka.name', 'label' => 'Оценка'],
                    ['attribute' => 'otziv', 'label' => 'Отзыв'],
                ];
            } else {
                $attributes = [
                    ['attribute' => 'id', 'label' => '№'],
                    ['attribute' => 'vidWork.name', 'label' => 'Вид изделия'], 
                    ['attribute' => 'navik.name', 'label' => 'Необходимый навык'],
                    ['attribute' => 'name', 'label' => 'Название'],
                    ['attribute' => 'cena', 'label' => 'Цена'],
                    ['attribute' => 'opisanie', 'label' => 'Описание'],
                    ['attribute' => 'reyting_start', 'label' => 'Необходимый рейтиг'],
                    ['attribute' => 'gorod', 'label' => 'Город'],
                    ['attribute' => 'poselok', 'label' => 'Поселок'],
                    ['attribute' => 'ulica', 'label' => 'Улица'],
                    ['attribute' => 'statusZakaz.name', 'label' => 'Статус заявки'],
                    ['attribute' => 'data_registry', 'label' => 'Дата регистрации'],
                    ['attribute' => 'data_start', 'label' => 'Дата выхода на объект'],
                    ['attribute' => 'data_end', 'label' => 'Дата завершения'],
                    ['attribute' => 'dolgota_change', 'label' => 'Долгота'],
                    ['attribute' => 'shirota_change', 'label' => 'Широта'],
                    [
                        'format' => 'html',
                        'value' => Html::img('/uploads/image/' . $model->image), 
                        'label' => 'Изображение'
                    ],         
                    ['attribute' => 'region.name', 'label' => 'Регион'],            
                ];
            }
        ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => $attributes
    ])?>   
    
    <?php 
        if ($role == 'master' && $model['id_status_zakaz'] == 1) {
            echo '<div style="margin-left : auto; margin-right : auto;">'
                . Html::a('Взять заявку', Yii::$app->urlManager->createUrl([
                        '/zakaz/activate-zakaz',
                    ]), 
                    [
                        'data' => [
                            'method' => 'post',
                            'params' => [
                                'id' => $model['id']
                            ]
                        ],
                        'class' => 'btn btn-warning btn-lg btn-block'
                    ]); 
        }
    ?>

</div>