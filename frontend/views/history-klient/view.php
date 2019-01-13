<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryKlient */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'History Klients', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-klient-view">

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
            'id_klient',
            'imya',
            'familiya',
            'otchestvo',
            'vozrast',
            'phone',
            'id_status_on_off',
            'reyting',
            'balans',
            'id_region',
            'old_id',
        ],
    ]) ?>

</div>
