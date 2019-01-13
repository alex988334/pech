<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Manager */

$this->title = 'Create Manager';
$this->params['breadcrumbs'][] = ['label' => 'Managers', 
    'url' => Yii::$app->urlManager->createUrl(['/manager/index', 'page' => Yii::$app->session->get('page') ?? '1'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="manager-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
