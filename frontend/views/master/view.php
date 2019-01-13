<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Master */

$this->title = '№' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Мастера', 
    'url' => Yii::$app->urlManager->createUrl(['/master/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="master-view">
    
    <p>
        <?php 
            if (Yii::$app->session->get('role') == common\models\User::MANAGER) {
                echo Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);
                echo Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]);
            }
        ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'id_master',
            [
                'value' => $model->user->username,
                'label' => 'Логин'
            ],
            'familiya',
            'imya',
            'otchestvo',
            [
                'value' => $model->statusOnOff->name,
                'label' => 'Статус подключения'
            ],
         //   'status_on_off_name',
            'vozrast',
            'staj',
            'reyting',
            [
                'value' => $model->statusWork->name,
                'label' => 'Статус работника'
            ],
         //   'status_work_name',
            'data_registry',
            'data_unregistry',
            'phone',
            'mesto_jitelstva',
            'mesto_raboti',
            'balans',
            [
                'value' => $model->region->name,
                'label' => 'Регион'
            ],
        //    'id_region',
            'limit_zakaz',
        ],
    ]) ?>

</div>
