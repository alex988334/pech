<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\MasterWorkNavik */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Master Work Naviks', 
    'url' => Yii::$app->urlManager->createUrl(['/navik/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;

debugArray($model->vidWork->name);
?>
<div class="master-work-navik-view">

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
        foreach ($model as $one) {
            echo DetailView::widget([
                'model' => $one,
                'attributes' => [
                //    'id',
                    'id_master',
                    [
                        'value' => $one->vidWork->name,
                        'label' => 'Вид работ'
                    ],
                   // 'id_vid_work',
                    [
                        'value' => $one->vidNavik->name,
                        'label' => 'Вид навыка'
                    ],
                    [
                        'value' => $one->vidNavik->sort,
                        'label' => 'Вес навыка'
                    ],

                ],
            ]);
        }
    ?>

</div>
