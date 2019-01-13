<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\SelectRegionWidget;

/*debugArray($model);
debugArray($regions);
debugArray($selectedRegion);
debugArray($vidiIzdeliy);
 // */
//  debugArray(Yii::$app->session->get('selectedRegion'));
$this->title = 'Заявки по группам';
//$this->params['breadcrumbs'][] = ['label' => 'Кабинет мастера', 'url' => ['/master/kabinet']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= '<div style="width : 700px; background-color : chocolate; margin-left : auto; '
    . 'margin-right : auto; border-radius : 20px";  >' ?>

<?= '<div style="margin-left : auto; margin-right : auto; padding : 25px;" >' ?>
<?= '<div style="border-radius : 20px; background-color : #f0ad4e; color : white; font-size: 16pt; text-align: center; '
        . 'padding : 10px; margin-top : 10px; margin-bottom : 15px; margin-left : auto; margin-right : auto; width : 550px;">'
        . 'Заявки сортированные по видам работ</div>';?>
<?= '<div style="display: inline-block">' ;?>
<?php     
    echo SelectRegionWidget::widget(['listRegion' => $regions, 'selectedRegion' => $selectedRegion]);
?>
<?php 
    echo '<div id="container_buttons" style="margin-top : 15px;">';
 //   Yii::debug($vidiIzdeliy);
 //   Yii::debug($model);
    foreach ($vidiIzdeliy as $vid) {
        $label = 'Вид : ' . $vid['name'] . ', Всего заявок : 0, Общая сумма работ : 0';
        foreach ($model as $zakaz){               
            if ($zakaz['id_vid_work'] == $vid['id']) {
                $label =  'Вид : ' . $vid['name'] . ', Всего заявок : ' 
                        . $zakaz['vsego'] . ', Общая сумма работ : '
                    . $zakaz['cena'];
                break;                
            }    
        }
        echo Html::a($label, Yii::$app->urlManager->createUrl([
                    '/zakaz/index', 
                    'id_vid_work' => $vid['id']
                ]), 
                [
                    'class' => 'btn btn-warning btn-lg btn-block', 
                    'id' => $vid['id']
                ]);         
    }
?>
<?= '</div>' ;?>
<?= '</div>' ;?>
<?= '</div>' ;?>
<?php
    $this->registerJsFile('@web/js/zakazi_vid.js', ['depends' => 'yii\web\YiiAsset']);

?>