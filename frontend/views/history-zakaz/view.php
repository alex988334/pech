<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\HistoryZakaz */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'History Zakazs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-zakaz-view">

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
            'id_zakaz',
            'id_vid_work',
            'id_navik',
            'name',
            'cena',
            'opisanie',
            'reyting_start',
            'zametka',
            'gorod',
            'poselok',
            'ulica',
            'dom',
            'kvartira',
            'id_status_zakaz',
            'id_shag',
            'data_registry',
            'data_start',
            'data_end',
            'dolgota',
            'shirota',
            'dolgota_change',
            'shirota_change',
            'image',
            'id_region',
            'id_ocenka',
            'otziv',
        ],
    ]) ?>

</div>
