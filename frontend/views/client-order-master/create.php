<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ClientOrderMaster */

$this->title = 'Create Client Order Master';
$this->params['breadcrumbs'][] = ['label' => 'Client Order Masters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-order-master-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
