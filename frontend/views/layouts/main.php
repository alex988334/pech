<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use common\models\VidRegion;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <?= Yii::$app->name = 'Печной мир'; ?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <!--link rel="icon" href="/favicon.ico" type="image/x-icon"-->
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap"> 
    <?php        
        $role = Yii::$app->session->get('role');
        if ($role == 'manager' || $role == 'head_manager') {    
            Yii::$app->homeUrl = '/manager/index';
            $menuItems = [   
                ['label' => 'Чат', 'url' => ['/chat/index']],   
                [
                    'label' => 'История',
                  //  ['label' => 'История', 'url' => []], 
                    'items' => [
                        ['label' => 'Клиенты', 'url' => ['/history-klient/index']],                 
                        '<li class="divider"></li>',                         
                        ['label' => 'Мастера', 'url' => ['/history-master/index']],   
                        '<li class="divider"></li>',                         
                        ['label' => 'Заявки', 'url' => ['/history-zakaz/index']],
                    ],
                ],
                ['label' => 'Пользователи', 'url' => ['/site/user']],                 
                ['label' => 'Менеджеры', 'url' => ['/manager/index']],                    
                ['label' => 'Заявки', 'url' => ['/zakaz/index']], 
                [
                    'label' => 'Клиенты',
                    'items' => [
                        ['label' => 'Клиенты', 'url' => ['/klient/index']],                 
                        '<li class="divider"></li>',                         
                        ['label' => 'Клиенты и заявки', 'url' => ['/klient-vs-zakaz/index']],
                
                    ],
                ],
                [
                    'label' => 'Мастера',
                    'items' => [
                        ['label' => 'Мастера', 'url' => ['/master/index']],                   
                        '<li class="divider"></li>',                         
                        ['label' => 'Мастера и заявки', 'url' => ['/master-vs-zakaz/index']], 
                        '<li class="divider"></li>',                         
                        ['label' => 'Навыки мастеров', 'url' => ['/navik/index']],     
                    ],
                ],      
            ];
        }
        if ($role == 'master') {
            Yii::$app->homeUrl = '/master/kabinet';
            $menuItems = [          
                ['label' => 'Чат', 'url' => ['/chat/index']],   
                ['label' => 'Кабинет', 'url' => ['/master/kabinet']],
                ['label' => 'Ваши навыки', 'url' => ['/navik/index']],
                ['label' => 'Заявки', 'url' => ['/zakaz/vid']],  
         //       ['label' => 'Настройки', 'url' => ['/master/settings']],     
            ];
        }
        if (Yii::$app->user->isGuest) {            
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        } else {
            $menuItems[] = '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выход (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
        }
        NavBar::begin([
            'brandImage' => '/images/logo50.png', //Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => $menuItems,
        ]);
        NavBar::end();
    ?>
        
    <div class="container">
        <?= Breadcrumbs::widget([
            'homeLink' => ($role == 'manager' || $role == 'head_manager') ?
            ['label' => 'Главная', 'url' => '/zakaz/index'] :
            ['label' => 'Главная', 'url' => '/master/kabinet'],
            'links' => isset($this->params['breadcrumbs'])
                ? $this->params['breadcrumbs'] : [],
            
            ]) ?>
        <?php // Alert::widget() ?>
        <?php  //Yii::$app->session->getFlash('message') ?>
        
        <?php 
            if (Yii::$app->session->getFlash('message')) { 
                echo yii\bootstrap\Alert::widget([
                'options' => [
                    'class' => 'alert-info',
                ],
                'body' => Yii::$app->session->getFlash('message'),
                ]);                
            }
        ?>
        
        <?= $content ?>
    </div>        
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
        <div class="pull-right">
            <?php 
                if ($role == 'manager' && $this->title != 'Чат') {    
                    echo '<div id="sp1" class="sp1">
                        <div class="sp1-head"><button id="openChat" class="btn btn-primary btn-block">Сообщения о заявках</button></div>
                        <div class="sp1-content" id="sp1-content"></div>
                    </div>';             
                }  
            ?>         
        </div>
    </div>
</footer>
<?php 
    $this->registerJsFile('@web/js/messenger.js', ['depends' => 'yii\web\YiiAsset', 'position' => \yii\web\View::POS_END]);
    $this->registerJs('let userName = "'. \common\models\User::find()->select(['username'])->where(['id' => Yii::$app->user->getId()])->scalar() 
        . '"; ', \yii\web\View::POS_HEAD);
    $this->registerJs(' securityWebSocket();', \yii\web\View::POS_LOAD);
    
    if ($role == 'manager' && $this->title != 'Чат') {    
        $this->registerJs(' $("#openChat").bind("click", function(){'
        . ' if ($("#sp1")[0].style.height == "40px") { $("#sp1")[0].style.height = "300px"; }'
        . ' else { $("#sp1")[0].style.height = "40px"; }});',
         \yii\web\View::POS_END);         
    }
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?> 