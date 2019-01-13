<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Klient */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Клиенты', 
    'url' => Yii::$app->urlManager->createUrl(['/klient/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;

//debugArray($model);
//debugArray($search);
?>
<div class="klient-view">
    
    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Продолжить удаление?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          //  'id',
            'id_klient',
            [
                'label' => 'Логин',
                'format' => 'html',
                'value' => $model['user']['username'] . Html::a('Сменить логин, пароль', 
                        '/site/change-login-password', ['class' => 'btn btn-warning', 'style' => 'margin-left: 10px;']),
            ],
            'imya',
            'familiya',
            'otchestvo',
            'vozrast',
            'phone',
            [
                'value' => $model->statusOnOff->name,
                'label' => 'Статус подключения'
            ],
            'reyting',
            'balans',
            [
                'value' => $model->region->name,
                'label' => 'Регион'
            ],
        ],
    ]) ?>

</div>
