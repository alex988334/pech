<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\HistoryMaster */

$this->title = 'Create History Master';
$this->params['breadcrumbs'][] = ['label' => 'History Masters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-master-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
