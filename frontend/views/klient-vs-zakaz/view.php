<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\KlientVsZakaz */

$this->title = 'Клиент №' . $model->id_klient . ' ->заявка №' . $model->id_zakaz;
$this->params['breadcrumbs'][] = ['label' => 'Клиенты и заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="klient-vs-zakaz-view">
    
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
            'id_klient',
            'id_zakaz',
        ],
    ]) ?>

</div>
