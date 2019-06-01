<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\jui\Dialog;
use common\models\Chat;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ChatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Чат';
//$this->params['breadcrumbs'][] = $this->title;
 
  //  if (isset($list)) debugArray($list);
 /*   if (isset($messages)) debugArray($messages);
    if (isset($users)) debugArray($users);
   
 // */
//debugArray($res);
?>

<div class="chat-index">
    
    <?php //Pjax::begin(); ?>    

    <p>
        <?php // Html::a('Create Chat', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="chat">        
        <div class="contacts-panel" id="contacts_panel">
            <div class="control-panel" id="control_panel">
                <div id="new_chat" class="control-panel-button" onclick="createFindUserDialog()" title="Новый чат"><img src="/images/new32.png"></div>
                <div id="leave_chat" class="control-panel-button" onclick="createExitChatDialog()" title="Покинуть чат"><img src="/images/leave32.png"></div>
                <div id="delete_chat" class="control-panel-button" onclick="createDeleteChatDialog()" title="Удалить чат"><img src="/images/delete32.png"></div>
                <div id="show_users" class="control-panel-button" onclick="createUsersOfChatDialog()" title="Список участников чата">
                    <img src="/images/list_users32.png">
                </div>
                <div id="black_list" class="control-panel-button" onclick="createBlackListDialog()" title="Черный список"><img src="/images/blackList32.png"></div>
            
            
            </div>
            <div class="contacts" id="contacts">                
            
        <?php
       
            foreach ($chats as $one) {
                if ($one['status'] == Chat::CHAT_DIACTIVATED) $status = 'style="background-color: grey; border-color: black;"';
                else $status = '';
                $autor = (Yii::$app->user->getId() == $one['autor']) ? '&copy;' : '';
                
                if (isset($messages[$one['id']])) {
                    $chatUser = (isset($users[$messages[$one['id']]['id_user']])) 
                                            ? $users[$messages[$one['id']]['id_user']]['username'] .': <i>'. $messages[$one['id']]['message'] .'</i>' : '';
                    $chatDate = strftime('%e %b', strtotime($messages[$one['id']]['date']));
                    if ($chatDate == strftime('%e %b')) $chatDate = $messages[$one['id']]['time'];
                    //   echo strftime('%e %b', strtotime($messages[$one['id']]['date']));
                } else {
                    $chatMessage = $chatDate = $chatUser = '';
                }
                                
                echo '<table id="'. $one['id'] .'" rows="2" cols="4" class="chat-user" onclick="getHistoryChat(this)" >
                        <tr>
                            <td class="user-td" id="chat_name" colspan="2" style="font-size: 11pt; width: auto;"><b>'. $one['alias']  .'</b></td>
                            <td class="user-td" id="chat_autor" style="width: 20px; padding-left: 5px;">'. $autor .'</td>
                            <td class="user-td" id="chat_alarm" style="width: 40px;">
                                <div id="count_message" class="alarm">
                                    <font id="text_count" style="text-align: center; vertical-align: middle;"></font>
                                </div>
                            </td>
                        </tr>
                        <tr>                            
                            <td colspan="2" class="user-td" id="last_message" style="padding-bottom: 10px; padding-top: 0px; font-size: 9pt;">'. $chatUser .'</td>
                            <td class="user-td" id="last_date" colspan="2" style="text-overflow: clip;
                                    padding-left: 4px; padding-top: 0px; padding-bottom: 10px; padding-right: 5px; font-size: 9pt; font-style: italic;">'. $chatDate .'</td>
                        </tr>
                    </table>';
                /*echo '<div id="'. $one['id'] . '" class="chat-user" onclick="getHistoryChat(this)" ' . $status . '>'
                        . '<div style="text-align: center; padding: 5px; padding-left:10px; max-width: 70%; display: inline-block;">' . $one['alias'] 
                        . '</div>'                        
                        . '<div id="count_message" class="alarm">'
                            . '<font id="text_count" style="text-align: center; vertical-align: middle;"></font>'
                        . '</div>'                        
                    . '</div>';*/
            }    
            
       /*     $js = 'securityWebSocket();            
                
            ';*/
            $this->registerAssetBundle(yii\web\JqueryAsset::className(), View::POS_HEAD);
            
          /*  $this->registerJs('let userName = "' . \common\models\User::find()->select(['username'])
                    ->where(['id' => Yii::$app->user->getId()])->scalar() . '";', yii\web\View::POS_END);  */          
            
            $this->registerCssFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', 
                    ['dependst' => 'yii\bootstrap\BootstrapAsset', /* 'position' => View::POS_END*/]); 
            $this->registerJsFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', 
                    ['dependst' => 'yii\web\YiiAsset', /* 'position' => View::POS_END*/]);
            
         /*   $this->registerJsFile('@web/js/messenger.js', [
                    'dependst' => 'yii\web\YiiAsset',
                    'position' => View::POS_END]); */
     //       $this->registerJs($js, yii\web\View::POS_END); 
            
        ?>
        
            </div>
        </div>
        <div class="message-container"></div>    
    </div>       
    <div class="editor" style="margin-top: 10px;">
        <div style="width: 100%; background-color: grey; visibility: hidden; margin-bottom: 4px;" id="progressDiv"><div id="progress_line" style="height: 5px; background-color: green; width: 0px;"></div></div>
        <div style="width: 82%; height: 30px; vertical-align: top; display: inline-block;">
            <input class="form-control" type="text" id="editor" style="width: 100%;">
        </div>
        <div style="display: inline-block; vertical-align: top; width: 15%;">
            <button onclick="document.querySelector('#inputFile').click();" class="btn btn-warning btn-sm" title="Прикрепить изображение"><img src="/images/attach_24.png"></img></button>
            <button id="send" onclick="sendMessage()" class="btn btn-warning btn-sm" title="Отправить"><img src="/images/send_24.png"></img></button>
            
            <input id="inputFile" type="file" style="display: none;">
            
        </div>        
    </div>    
</div>   



<?php //Pjax::end(); ?>

