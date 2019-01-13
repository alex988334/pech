<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryMaster */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'History Masters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-master-view">

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'date',
            'time',
            'id_status_history',
            'role',
            'username',
            'id_user',
            'id_master',
            'familiya',
            'imya',
            'otchestvo',
            'id_status_on_off',
            'vozrast',
            'staj',
            'reyting',
            'id_status_work',
            'data_registry',
            'data_unregistry',
            'phone',
            'mesto_jitelstva',
            'mesto_raboti',
            'balans',
            'id_region',
            'limit_zakaz',
            'old_id',
        ],
    ]) ?>

</div>
