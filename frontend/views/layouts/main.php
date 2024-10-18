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
use yii\web\View;

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
    <?php 
        $this->head();
        
       /* $this->registerAssetBundle(yii\web\JqueryAsset::className(), View::POS_HEAD);
        $this->registerCssFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', 
                    ['dependst' => 'yii\bootstrap\BootstrapAsset',  'position' => View::POS_BEGIN]); 
        $this->registerJsFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', 
                ['dependst' => 'yii\web\YiiAsset', 'position' => View::POS_BEGIN]);
        */
        $user = \common\models\User::find()->select(['id', 'username'])->where(['id' => Yii::$app->user->getId()])->one();
        if ($user != null) {
            $this->registerJs('let userName = "'. $user->username . '"; var idUser =' 
                    . $user->id .';', \yii\web\View::POS_HEAD);
        }
        ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap"> 
    <?php        
        $role = Yii::$app->session->get('role');
        if ($role == 'manager' || $role == 'head_manager') {    
            Yii::$app->homeUrl = '/manager/index';
            $menuItems = [   
            //    ['label' => 'Чат', 'url' => ['/chat/index']],   
                [
                    'label' => 'История',
                  //  ['label' => 'История', 'url' => []], 
                    'items' => [
                        ['label' => 'Клиенты', 'url' => ['/history-klient/index']],                 
                        '<li class="divider"></li>',                         
                        ['label' => 'Мастера', 'url' => ['/history-master/index']],   
                        '<li class="divider"></li>',                         
                        ['label' => 'Заявки', 'url' => ['/history-zakaz/index']],
                        '<li class="divider"></li>',                         
                        ['label' => 'Вход в панель', 'url' => ['/history/index']],
                    ],
                ],
                ['label' => 'Пользователи', 'url' => ['/site/user']],                 
                ['label' => 'Менеджеры', 'url' => ['/manager/index']],   
                ['label' => 'Клиент-заявка-мастер', 'url' => ['/client-order-master/index']], 
                ['label' => 'Заявки', 'url' => ['/zakaz/index']], 
                [
                    'label' => 'Клиенты',
                 //   'items' => 
                    'url' => ['/klient/index'],
            //        [['label' => 'Клиенты', 'url' => ['/klient/index']],                 
           //             '<li class="divider"></li>',                         
            //            ['label' => 'Клиенты и заявки', 'url' => ['/klient-vs-zakaz/index']],
                
            //        ],
                ],
                [
                    'label' => 'Мастера',
                    'items' => [
                        ['label' => 'Мастера', 'url' => ['/master/index']],                   
                        '<li class="divider"></li>',                         
                      //  ['label' => 'Мастера и заявки', 'url' => ['/master-vs-zakaz/index']], 
                    //    '<li class="divider"></li>',                         
                        ['label' => 'Навыки мастеров', 'url' => ['/navik/index']],     
                    ],
                ],      
            ];
        }
        if ($role == 'master') {
            Yii::$app->homeUrl = '/master/kabinet';
            $menuItems = [          
             //   ['label' => 'Чат', 'url' => ['/chat/index']],   
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
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            
            ]) ?>
        <?php //  Alert::widget() ?>
        <?php // Yii::$app->session->getFlash('message') ?>
        
        <?php 
            if ($msg = Yii::$app->session->getFlash('message')) { 
                echo yii\bootstrap\Alert::widget([
                'options' => [
                    'class' => 'alert-info',
                ],
                'body' => $msg,
                ]);                
            }
        ?>
        
        <?= $content ?>
    </div>        
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
        <?php
        if (!Yii::$app->user->isGuest) echo 
        '<div class="messenger" id="messenger">            
            <div class="collapse" id="messengerBody" style="width: 650px; padding: 5px; background-color: #eae7e7; border: solid 3px #993300; border-radius: 5px;">
                <div class="left-panel" id="left-panel">   
                    <div class="control-panel" id="control_panel">
                        <div id="new_chat" class="control-panel-button" onclick="createFindUserDialog()" title="Новый чат"><img src="/images/new32.png"></div>
                        <div id="leave_chat" class="control-panel-button" onclick="createExitChatDialog()" title="Покинуть чат"><img src="/images/leave32.png"></div>
                        <div id="delete_chat" class="control-panel-button" onclick="createDeleteChatDialog()" title="Удалить чат"><img src="/images/delete32.png"></div>
                        <div id="show_users" class="control-panel-button" onclick="createUsersOfChatDialog()" title="Список участников чата">
                            <img src="/images/list_users32.png">
                        </div>
                        <div id="black_list" class="control-panel-button" onclick="createBlackListDialog()" title="Черный список"><img src="/images/blackList32.png"></div>
                        <div id="update_chats" class="control-panel-button" onclick="updateChats()" title="Обновить список чатов"><img src="/images/update32.png"></div>
                    </div>
                    <div class="chats" id="chats"></div>                                                                                           
                </div>
                <div class="right-panel" id="right-panel">
                    <button id="showLeftPanel" onclick="showLeftPanel()" style="margin: 3px; border: none; background-color: #eae7e7;"><img src="/images/visible16.png"></img></button>
                    <div class="message-container"></div> 
                </div>
                <div class="bottom-panel" id="bottom-panel">
                    <div style="width: 100%; background-color: grey; visibility: hidden; margin-bottom: 4px;" id="progressDiv">
                        <div id="progress_line" style="height: 5px; background-color: green; width: 0px;"></div>                            
                    </div>
                    <div id="editorContainer" style="vertical-align: top; display: inline-block; width: 80%;">
                        <input class="form-control" type="text" id="editor" style="width: 100%;">
                    </div>
                    <div id="editorButtonsContainer" style="display: inline-block; vertical-align: top; width: 17%">
                        <button onclick="document.querySelector(\'#inputFile\').click();" class="btn btn-warning btn-sm" title="Прикрепить изображение"><img src="/images/attach_24.png"></img></button>
                        <button id="send" onclick="sendMessage()" class="btn btn-warning btn-sm" title="Отправить"><img src="/images/send_24.png"></img></button>
                        <input id="inputFile" type="file" style="display: none;">
                    </div>        
                </div>       
            </div>
            <button id="openChat" class="btn btn-primary" style="float: right; border-radius: 50px; padding: 20px; 
                    transition: padding 0.2s; background-color: #993300;" data-toggle="collapse" data-target="#messengerBody">Чат        
                <div id="head_alarm" class="alarm" style="visibility: visible; display: none;"></div>
            </button>
        </div>';  
        ?>            
    </div>
</footer>
<?php    

    if (!Yii::$app->user->isGuest)
            $this->registerJsFile('@web/js/messenger.js', ['depends' => 'yii\web\YiiAsset', 'position' => \yii\web\View::POS_END]);
    
  //  $this->registerJs(' securityWebSocket();', \yii\web\View::POS_LOAD);
    
   // if ($role == 'manager' && $this->title != 'Чат') {    
    /*    $this->registerJs(' ',
         \yii\web\View::POS_END);         
  //  }*/
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?> 