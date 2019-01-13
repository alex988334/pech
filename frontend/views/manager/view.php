<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\AuthItem;

/* @var $this yii\web\View */
/* @var $model common\models\Manager */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Managers', 
    'url' => Yii::$app->urlManager->createUrl(['/manager/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="manager-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php
        if (Yii::$app->session->get('role') == AuthItem::MANAGER) {
            $mass = ['id_manager', 'familiya', 'imya', 'otchestvo', 'id_region',
                'phone1', 'phone2', 'phone3'];
        } else {
            $mass = [
                [
                    'label' => 'Логин',
                    'format' => 'html',
                    'value' => $model['user']['username'] . Html::a('Сменить логин, пароль', 
                            '/site/change-login-password', ['class' => 'btn btn-warning', 'style' => 'margin-left: 10px;']),
                ],
                'id_manager', 
                'familiya', 
                'imya', 
                'otchestvo', 
                'id_region',
                'phone1', 
                'phone2', 
                'phone3'
            ];
        }
        
        echo DetailView::widget([
            'model' => $model,
            'attributes' => $mass
        ]) 
    ?>

</div>
