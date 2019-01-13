<?php
namespace console\daemons;

use Yii;
use consik\yii2websocket\events\WSClientMessageEvent;
use consik\yii2websocket\WebSocketServer;
use Ratchet\ConnectionInterface;

use common\models\Chat;
use common\models\ChatUser;
use common\models\ChatMessage;
use common\models\ChatMessageStatus;
use common\models\ChatBlackList;
use common\models\User;
use common\models\Message;

use yii\db\Query;

use console\controllers\ServerController;
use yii\helpers\Json;

use common\models\AuthItem;
use common\models\AuthAssignment;
use yii\helpers\ArrayHelper;


class EchoServer extends WebSocketServer
{
    const STATUS_ACCEPT = 1;
    const STATUS_ERROR = 0;
    
    const ERROR_USER_NAME = 1001;    
    const ERROR_WRITE_BASE = 1002;
    const ERROR_SEND_MESSAGE = 1003; 
    const ERROR_SEND_PARAMETR = 1004;
    
    const OP_STATUS_MESSAGE = 101;
    const OP_INPUT_MESSAGE = 102;
    const OP_OUTPUT_MESSAGE = 103;
    const OP_SET_USER_NAME = 104;  
    const OP_LIST_USERS = 105;
    const OP_CREATE_NEW_CHAT = 106;
    const OP_WRITEN = 107;
    
    
    const ZAKAZ_AKTIVATE = 150;
    const ZAKAZ_DIAKTIVATE = 151;
    
    const MESSAGE_ALL = 1010;
    
    const MESSAGE_SEND = 111;
    const MESSAGE_DELIVERED = 112;
    const MESSAGE_READED = 113; 
    
       
    /**
     *
     * @var console\controllers\ServerController $controller
     */
    public $controller;
    
    
    public function init()
    {
        parent::init();
        
        $this->on(self::EVENT_CLIENT_MESSAGE, function (WSClientMessageEvent $e) {
            $e->client->send( $e->message );
        });
    }
    
    protected function getCommand(ConnectionInterface $from, $msg)
    {  
        $request = json_decode($msg, true);         
        return !empty($request['action']) ? $request['action'] : parent::getCommand($from, $msg);
    }
    
    
    public function commandName(ConnectionInterface $client, $msg)
    {
        $client->send(json_encode(['operation'=> 'name', 'name' => $client->name, 'id_user' => $client->idUser]));
    }
    
    
    public function commandSystem(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandSystem -> START', ServerController::LOG_WARNING);        
        $request = json_decode($msg, true); 
        
        if (isset($request['id']) && isset($request['status'])){
         
            try {
                $master = Yii::$app->db->createCommand('SELECT id_master, CONCAT(familiya, " ", imya, " "'
                        . ', otchestvo) AS fio FROM master WHERE id_master=' . $client->idUser)->queryOne();
                
                $managerId = Yii::$app->db->createCommand('SELECT mg.id_manager FROM `manager` mg, master m, auth_assignment a '
                        . ' WHERE m.id_region=mg.id_region AND a.user_id=mg.id_manager AND a.item_name="'
                        . AuthItem::MANAGER . '" AND m.id_master=' . $client->idUser)->queryScalar();
                
                $chatId = Yii::$app->db->createCommand('SELECT c.id_chat FROM chat_user c LEFT JOIN chat_user u '
                        . 'ON c.id_chat=c.id_chat WHERE c.id_user=0 AND u.id_user=' . $managerId)->queryScalar();
                
                $date = date('Y-m-d');
                if (!$chatId) {
                   
                    $chat = new Chat();
                    $chat->autor = 0;
                    $chat->alias = 'System';
                    $chat->create_at = $date;
                    $chat->status = Chat::CHAT_ACTIVE;
                    
                    if ($chat->save()) {
                     
                        Yii::$app->db->createCommand()->batchInsert('chat_user', ['id_chat', 'id_user'],
                                [[$chat->id, 0], [$chat->id, $managerId]])->execute();
                    } else {
                        $this->controller->log('CHAT NE SOZDAN' , ServerController::LOG_ERROR); 
                    } 
                }
            } catch (\Exception $ex) {          
                $this->controller->log('commandSystem -> ERROR $ex = ' . $ex->getMessage(), ServerController::LOG_ERROR); 
                $this->controller->log('commandSystem -> END', ServerController::LOG_WARNING); 
                return;
            }
            
            $message = new ChatMessage();
            $message->id_chat = $chatId;
            $message->id_user = 0;
            switch ($request['status']) {
                case self::ZAKAZ_AKTIVATE: $strM = 'Запрос мастера №'. $master['id_master'] 
                       . ' '. $master['fio'] . ' на взятие заявки №' . $request['id'] ;
                    break;
                case self::ZAKAZ_DIAKTIVATE: $strM = 'Запрос мастера №'. $master['id_master'] 
                        . ' '. $master['fio'] . ' на отказ от заявки №' . $request['id'] ;
                    break;
            }
            $message->message = $strM;
            $message->date = $date;
            $message->time = date('H:i:s');  
            
            try {               
                if ($message->validate() && $message->save()) {

                    $send = ChatMessageStatus::MESSAGE_SEND;
                    foreach ($this->clients as $one) {
                        if ($one->idUser == $managerId) {
                            
                            $one->send(json_encode(Message::createOfArray(['status' => self::STATUS_ACCEPT,
                                'operation' => self::OP_INPUT_MESSAGE, 'id_chat' => $message->id_chat, 
                                'id' => $message->id, 'autor' => 'System', 'id_autor' => 0, 'id_user' => $client->idUser,
                                'message' => $message->message, 'date' => $message->date, 'time' => $message->time])));

                            $send = ChatMessageStatus::MESSAGE_DELIVERED;
                            break;
                        }
                    }
                    Yii::$app->db->createCommand()->batchInsert('chat_message_status', 
                            ['id_message', 'id_user', 'status_message', 'date', 'time'],
                            [[$message->id, 0, $send, $message->date, $message->time], 
                            [$message->id, $managerId, $send, $message->date, $message->time]])->execute();
              
                }else {
                    $this->controller->log('_SOOBSHENIE NE SOXRANENO_', ServerController::LOG_ERROR); 
                }
            } catch (\Exception $ex) {          
                $this->controller->log('commandSystem -> ERROR $ex = ' . $ex->getMessage(), ServerController::LOG_ERROR); 
                $this->controller->log('commandSystem -> END', ServerController::LOG_WARNING); 
            }
        }
        $this->controller->log('commandSystem -> END', ServerController::LOG_WARNING);        
    }
    
    
    protected function securityMessage(ConnectionInterface $client, string $msg)
    {      
        $this->controller->log('securityMessage -> START', ServerController::LOG_WARNING);        
        $request = json_decode($msg, true);  
        
        if (!isset($client->name) || !isset($client->idUser) 
                || !isset($request['message']) || !isset($request['id_chat'])) {
            $this->controller->log('Security -> error client parametr!!!', ServerController::LOG_ERROR);
            $this->controller->log('securityMessage -> END', ServerController::LOG_WARNING);
            return null;
        }
        
        $request['id_autor'] = $client->idUser;
        $request['autor'] = $client->name;
        $request['date'] = date('Y-m-d');
        $request['time'] = date('H:i:s');
        
        $this->controller->log('securityMessage -> END', ServerController::LOG_WARNING);
        return $request;
    }

    
    public function saveMessage($request)
    {
        $this->controller->log('saveMessage -> START', ServerController::LOG_WARNING);        
        
        $newMessage = new ChatMessage();
        $newMessage->setAttributes($request);
        $newMessage->id_user = $request['id_autor'];
        $usersNames = ChatUser::find()->select(['id_user', 'username'])
                ->where(['id_chat' => $newMessage->id_chat])
                ->join('INNER JOIN', 'user', 'user.id=chat_user.id_user')
                ->with('blackList')->asArray()->all(); 
        
        if (!$newMessage->save()) {
            $this->controller->log('SAVE MESSAGE -> error!!!', ServerController::LOG_ERROR);
            $this->controller->log('saveMessage -> END', ServerController::LOG_WARNING);
            return null;
        }
        
        $data = [];
        foreach ($usersNames as $user) $data[] = [ $newMessage->id, $user['id_user'], 
                    ChatMessageStatus::MESSAGE_SEND, $newMessage->date, $newMessage->time
                ];   
        
        try {                        
            Yii::$app->db->createCommand()->batchInsert('chat_message_status', 
                        ['id_message', 'id_user', 'status_message', 'date', 'time'], $data)->execute();              
        } catch (\Exception $ex) {
            $this->controller->log('Status save -> ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);
            $this->controller->log('saveMessage -> END', ServerController::LOG_WARNING);
            return null;
        }
        $request['id'] = $newMessage->id;
        $this->controller->log('saveMessage -> END', ServerController::LOG_WARNING);
       
        return [$usersNames, $request];
    }
    
    
    protected function sendNewMessage(array $usersNames, Message $newMessage)
    {
        $this->controller->log('sendNewMessage -> START', ServerController::LOG_WARNING);
        
        $newMessage->operation = self::OP_INPUT_MESSAGE;
        $newMessage->status = self::STATUS_ACCEPT;
        $newMessage->s_code = ChatMessageStatus::MESSAGE_SEND;
        $newMessage->s_message = 'Новое сообщение';   
        
        foreach ($usersNames as $user){                
            foreach ($this->clients as $chatClient) {
                if ($chatClient->name == $user['username']) {                   //  не обрабатывается вариант если клиент не в сети, т.е. его нет в пуле
                    $lock = true;
                    foreach ($user['blackList'] as $locked) {
                        $lock = true;
                        if ($locked['locked'] == $newMessage->id_autor) {
                            $lock = false;
                            break;
                        }                        
                    }
                    $newMessage->id_user = $chatClient->idUser;
                    
                    if ($lock) {
                        if ($newMessage->id_autor != $newMessage->id_user) $status = ChatMessageStatus::MESSAGE_DELIVERED;
                        else $status = ChatMessageStatus::MESSAGE_SEND;
                        $chatClient->send(json_encode($newMessage));
                    } else { 
                        $status = ChatMessageStatus::MESSAGE_BLACK_LIST;                         
                    }  
                    try {
                        Yii::$app->db->createCommand()->update('chat_message_status', 
                                ['status_message' => $status], 'id_message=' 
                                . $newMessage->id . ' AND id_user=' . $newMessage->id_user)->execute();
                    } catch (\Exception $ex) {
                        $this->controller->log('Status send -> error434!!!' . $ex->getMessage(), ServerController::LOG_ERROR);       
                    }                 
                    break;                        
                } 
            }                
        }    
        $this->controller->log('sendNewMessage -> END', ServerController::LOG_WARNING);
    }
    
    
    public function commandChat(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandChat -> START', ServerController::LOG_WARNING);      
        try {
            if (!$request = $this->securityMessage($client, $msg)) {
                $this->controller->log('commandChat -> END', ServerController::LOG_WARNING);
                return;             
            }                 
            if (!$request = $this->saveMessage($request)) {    
                $this->controller->log('commandChat -> END', ServerController::LOG_WARNING);
                return;             
            } 
        } catch (\Exception $ex) {
            $this->controller->log('EXCEPTION code: ' . $ex->getCode() . '; message : ' . $ex->getMessage(), ServerController::LOG_WARNING);
        }
        $usersNames = $request[0];
        $request = $request[1];
        
        $newMessage = Message::createOfArray($request);
        
        $this->sendNewMessage($usersNames, $newMessage);
      
        $this->controller->log('commandChat -> END', ServerController::LOG_WARNING);
    }   

    /**
     * Функция проверяет состояние статусов автора сообщения и при необходимости меняет его и отправляет сообщение
     * @param array $request Сообщение запроса со сменой статуса,
     *  содержит ключи ['id_chat' => ..., 's_code' => ChatMessageStatus::MESSAGE_DELIVERED, 'id_user' => ...], проверяет все сообщения чата,
     * так так клиенту грузится вся история чата. Если $allChat = true, то массив не затрагивается и может быть пустым
     * @param bool $allChat Флаг проверки всех чатов, если клиент подключился и еще не выбрал ни один чат 
     * (сигнализирует клиенту о пропущенных сообщениях и сигнализирует автору, что сообщения доставлены
     */   
    protected function securityStatus(array $request, bool $allChat = false, $client = null)
    {
        $this->controller->log('securityStatus -> START', ServerController::LOG_WARNING);
        if ($allChat) {
            $whereChat = '';  
            $query = 'SELECT m.id, m.id_chat, ms.id_user, ms.status_message AS s_code FROM chat_message_status ms, '
                    . ' chat_message m  WHERE m.id=ms.id_message AND ms.id_user=' . $request['id_user'] 
                    . ' AND m.id_user <> '. $request['id_user'] .' AND status_message IN ("' . ChatMessageStatus::MESSAGE_SEND 
                    . '", "' . ChatMessageStatus::MESSAGE_DELIVERED.'")';
            $query1 = 'UPDATE chat_message_status ms, chat_message m, chat_user u SET status_message="' 
                    . $request['s_code'] . '" WHERE  m.id=ms.id_message AND ms.id_user=u.id_user '
                    . ' AND status_message <> "' . ChatMessageStatus::MESSAGE_READED 
                    . '" AND u.id_user=' . $request['id_user'] /*. ' AND m.id_chat=' . $request['id_chat'] */
                    . ' AND ms.id_user <> m.id_user';
            try {
                $messagesChanged = Yii::$app->db->createCommand($query)->queryAll();  
                Yii::$app->db->createCommand($query1)->execute();  
            } catch (\Exception $ex) {
                $this->controller->log('Status change ONE -> ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);   
                $this->controller->log('securityStatus -> END', ServerController::LOG_WARNING);
                return;
            } 
            if ($client !== null) {
                foreach($messagesChanged as $sended) {
                    $sended['operation'] = self::OP_STATUS_MESSAGE;
                    $client->send(json_encode($sended));
                }
            }
        } else {
            $whereChat = ' AND m.id_chat=' . $request['id_chat']; 
        } 
        try{
            $messages = Yii::$app->db->createCommand('SELECT m.id, m.id_user, ms.status_message, m.id_chat '
                    . ' FROM chat_message_status ms JOIN chat_message m '
                    . ' ON m.id=ms.id_message WHERE m.id_user=ms.id_user AND status_message <> "' 
                    . ChatMessageStatus::MESSAGE_READED . '"' . $whereChat )->queryAll();
            
            foreach ($messages as $one) {
                
                $total = Yii::$app->db->createCommand('SELECT count(id_message) AS total, status_message FROM chat_message_status '
                        . 'WHERE id_message=' . $one['id'] .' GROUP BY status_message')->queryAll();
            
                $total = ArrayHelper::map($total, 'status_message', 'total');
            
                $block = false;
                
                $send = $total[ChatMessageStatus::MESSAGE_SEND] ?? 0;
                $delivered = $total[ChatMessageStatus::MESSAGE_DELIVERED] ?? 0;
                $readed = $total[ChatMessageStatus::MESSAGE_READED] ?? 0;
                $blocked = $total[ChatMessageStatus::MESSAGE_BLACK_LIST] ?? 0;
                
                switch($one['status_message']) {
                    case ChatMessageStatus::MESSAGE_SEND :                     
                        if ($send == 1) {
                            $block = true;
                            if ($delivered == 0) $status = ChatMessageStatus::MESSAGE_READED;
                            else $status = ChatMessageStatus::MESSAGE_DELIVERED;                     
                        }
                        break;
                    case ChatMessageStatus::MESSAGE_DELIVERED :
                        $this->controller->log('METKA_5');
                        if ($send == 0 && $delivered == 1) {
                            $block = true;
                            $status = ChatMessageStatus::MESSAGE_READED;                      
                        }
                        break;
                    default : {
                        $this->controller->log('securityStatus ERROR STATUS==DEFAULT');
                        $this->controller->log('securityStatus -> END', ServerController::LOG_WARNING);
                        return;                        
                    }
                }
                if ($block) {                             

                    Yii::$app->db->createCommand()->update('chat_message_status', 
                        ['status_message' => $status], 
                        'id_message=' . $one['id'] . ' AND id_user=' . $one['id_user'])->execute();               
                    foreach ($this->clients as $oneClient) {                    
                        $newStatus = Message::createOfArray([
                            'operation' => self::OP_STATUS_MESSAGE, 
                            'id_chat' => $one['id_chat'],
                            'id' => $one['id'],
                            's_code' => $status,                            
                            ]);                  
                        if ($oneClient->idUser == $one['id_user']) {
                            $oneClient->send(json_encode($newStatus));
                            $this->controller->log('OTPRAVKA AVTORU -> END');
                        }
                    }
                }                
            }            
        } catch (\Exception $ex) {
            $this->controller->log('SECURITY_STATUS  ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);   
            $this->controller->log('securityStatus -> END', ServerController::LOG_WARNING);
            return;
        }     
        $this->controller->log('securityStatus -> END', ServerController::LOG_WARNING);
    }

    public function commandStatus(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandStatus -> START', ServerController::LOG_WARNING);
        
        $request = json_decode($msg, true);
        if (!isset($request['id']) || !isset($request['id_chat']) || !isset($request['s_code']) 
                || !isset($client->name) || !isset($client->idUser)) {
            $this->controller->log('Status  ERROR PARAMETR1!!!', ServerController::LOG_ERROR);
            $this->controller->log('commandStatus -> END', ServerController::LOG_WARNING);
            return;
        }
        switch ($request['s_code']){
            case self::MESSAGE_ALL :
            case ChatMessageStatus::MESSAGE_DELIVERED : 
            case ChatMessageStatus::MESSAGE_READED : ;
                break;
            default : { 
                $this->controller->log('Status ERROR PARAMETR2!!!', ServerController::LOG_ERROR);
                $this->controller->log('commandStatus -> END', ServerController::LOG_WARNING);
                return; 
            }
        }
        
        $request['operation'] = self::OP_STATUS_MESSAGE;
        $request['status'] = self::STATUS_ACCEPT;        
        
        if ($request['s_code'] != self::MESSAGE_ALL) {            
            $query = 'UPDATE chat_message_status SET status_message="' . $request['s_code'] 
                    . '" WHERE id_message=' . $request['id'] . ' AND id_user=' . $client->idUser;
        } else {             
            $query = 'UPDATE chat_message_status ms, chat_message m, chat_user u SET status_message="' 
                    . ChatMessageStatus::MESSAGE_READED . '" WHERE  m.id=ms.id_message AND ms.id_user=u.id_user '
                    . ' AND status_message <> "' . ChatMessageStatus::MESSAGE_READED 
                    . '" AND u.id_user=' . $client->idUser . ' AND m.id_chat=' . $request['id_chat'] 
                    . ' AND ms.id_user <> m.id_user';
        }
        try {
            Yii::$app->db->createCommand($query)->execute();  
        } catch (\Exception $ex) {
            $this->controller->log('Status change ONE -> ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);   
            $this->controller->log('commandStatus -> END', ServerController::LOG_WARNING);
            return;
        }       
        
        $this->securityStatus($request);
      
        /*
         
         SELECT * FROM (SELECT id_message, ms.id_user, status_message FROM chat_message_status ms, chat_user u WHERE u.id_user=ms.id_user AND ms.id_user=380 AND u.id_chat=27) wer
JOIN chat_message ON chat_message.id=wer.id_message

UPDATE chat_message_status ms, chat_user u SET status_message='delivered' WHERE u.id_user=ms.id_user AND ms.id_user=380 AND u.id_chat=27  
         */    
        $this->controller->log('commandStatus -> END', ServerController::LOG_WARNING);
    }  

    
    public function commandPing(ConnectionInterface $client, $msg)
    {   
        $this->controller->log('PING', ServerController::LOG_WARNING);
        
        $client->send( json_encode('pong') );        
    }
    
    
    public function commandSetName(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandSetName -> START', ServerController::LOG_WARNING);
        $request = json_decode($msg, true);    

        if (!empty($request['name']) && $name = trim($request['name'])) { 
            $userId = User::find()->select(['id'])->where(['username' => $name])->scalar();  
            if ($userId) {                
                $client->name = $name;
                $client->idUser = $userId;                
            } 
        } else {
            $this->controller->log('commandSetName - ERROR NAME NULL', ServerController::LOG_ERROR);            
            $this->controller->log('commandSetName -> END', ServerController::LOG_WARNING);
            return;
        }
        
       /* $client->send( json_encode($result) );*/
        
        $this->securityStatus(['id_user' => $client->idUser, 
                's_code' => ChatMessageStatus::MESSAGE_DELIVERED], TRUE, $client);   
        
        $this->controller->log('commandSetName -> END', ServerController::LOG_WARNING);
    }
    
    
    public function commandListChatUsers(ConnectionInterface $client, $msg)
    {        
        $this->controller->log('commandListChatUsers -> START', ServerController::LOG_WARNING);
        $request = json_decode($msg, true);       
        
        if (!isset($request['id'])) {
            $client->send(json_encode(['operation' => self::OP_LIST_USERS, 'status' => self::STATUS_ERROR, 's_message' => 'Не заданы параметры']));
            
            $this->controller->log('commandListChatUsers ERROR Не заданы параметры', ServerController::LOG_ERROR);
            $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
            return;
        }
        $id = $request['id'];
     
        try {
            $users = Yii::$app->db->createCommand('SELECT id_user, autor FROM chat_user u, chat c WHERE u.id_chat=c.id AND id_chat=' . $id)->queryAll();
            $usersId = ArrayHelper::getColumn($users, 'id_user');
            $roles = AuthAssignment::find()->where(['user_id' => $usersId])->asArray()->all();
            
            reset($roles);
            while ($one = current($roles)){
                switch ($one['item_name']){
                    case AuthItem::HEAD_MANAGER:
                    case AuthItem::MANAGER: $table = 'manager';
                        $field = 'id_manager';
                        break;
                    case AuthItem::MASTER: $table = 'master';
                        $field = 'id_master';
                        break;
                    case AuthItem::KLIENT: $table = 'klient';
                        $field = 'id_klient';                    
                        break;
                    default: 
                        $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не опознанный пользователь!!!']));
                        $this->controller->log('commandListChatUsers ERROR Не опознанный пользователь!!!', ServerController::LOG_ERROR);
                        $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
                        return;                        
                    }
                $query = Yii::$app->db->createCommand('SELECT username, CONCAT(p.familiya, " ", p.imya, " ", p.otchestvo) AS fio '
                        . 'FROM '. $table .' p, user u WHERE p.'. $field .'=u.id AND u.id=' . $one['user_id'])->queryOne();

                $roles[key($roles)]['fio'] = $query['fio'];
                $roles[key($roles)]['username'] = $query['username']; 
                
                $roles[key($roles)]['connected'] = false; 
                
                foreach ($this->clients as $oneClient){
                    if ($oneClient->idUser == $one['user_id']) $roles[key($roles)]['connected'] = true; 
                }
                
                next($roles);
            }
            
            $blocked = ChatBlackList::find()->where(['blocking' => $client->idUser, 'locked' => $usersId])->asArray()->all();
            
        } catch (\Exception $ex) {
            $client->send(json_encode(['operation' => self::OP_LIST_USERS, '$blocked' => $blocked, '$roles' => $roles, '$users' => $users, 'status' => self::STATUS_ERROR, 's_message' => 'Ошибка поиска в бд']));
            $this->controller->log('commandListChatUsers EROR Ошибка поиска в бд', ServerController::LOG_ERROR);
            $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
            return;            
        }
        
        $client->send(json_encode(['operation' => self::OP_LIST_USERS, 'status' => self::STATUS_ACCEPT, 'chat_users' => $roles, 'black_list' => $blocked, 'id' => $client->idUser, 'autor' => $users[key($users)]['autor']]));
        
        $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
    }
    
    
    public function commandCreateChat(ConnectionInterface $client, $msg)
    {       
        $this->controller->log('commandCreateChat -> START', ServerController::LOG_WARNING);
        $request = json_decode($msg, true);      
        
// if (!Yii::$app->request->isAjax) return;
                
        if (!(isset($request['chat_name']) && $name = trim($request['chat_name']))){            
         //   return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Название чата пусто']);
        }
        if (!(isset($request['users']) && count($request['users']) > 0)){
        //    return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не указаны пользователи']);
        }
        
        $chat = new Chat();
        $chat->autor = $client->idUser;
        $chat->create_at = date('Y-m-d');
        $chat->status = Chat::CHAT_ACTIVE;
        if (strlen($name) > 30) $name = substr($name, 0, 30);
        $chat->alias = $name;
        
        $request['users'][] = $chat->autor;
        if ($chat->save()) {
            foreach($request['users'] as $one) {
                $chatUser = new ChatUser();
                $chatUser->id_chat = $chat->id;
                $chatUser->id_user = $one;
                
                if ($chatUser->save()) {
                    foreach ($this->clients as $one) {
                        if ($one->idUser == $chatUser->id_user) {
                            $one->send(json_encode(Message::createOfArray([
                                'operation' => self::OP_CREATE_NEW_CHAT,
                                'status' => self::STATUS_ACCEPT,
                                'id_chat' => $chatUser->id_chat,
                                'id_autor' => $client->idUser,
                                'id_user' => $chatUser->id_user,
                                'message' => $chat->alias
                            ])));
                            break;
                        }
                    }                    
                }
            }
        }        
    //    return json_encode(['status' => self::STATUS_ACCEPT, 'id' => $chat->id, 'name' => $name]);
    }
    
    
    public function commandUserWrite(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandUserWrite -> START ', ServerController::LOG_WARNING);   
        $request = json_decode($msg, true);    
        if (!isset($request['id_chat'])){
            $this->controller->log('commandUserWrite -> ERROR!!! id_chat=NULL', ServerController::LOG_ERROR);   
        }
        
        try {
            $users = ChatUser::find()->select(['id_chat', 'id_user', 'username'])
                    ->where(['id_chat' => $request['id_chat']])
                    ->join('INNER JOIN', 'user', 'user.id=chat_user.id_user')->asArray()->all();
        } catch (\Exception $ex) {
            $this->controller->log('commandUserWrite -> ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);   
        }
        
        foreach ($users as $user){
            if ($user['id_user'] == $client->idUser) $autor = $user['username'];
        }
        
        foreach ($users as $user){
            if ($user['id_user'] == $client->idUser) continue;
            
            foreach ($this->clients as $one){
                if ($user['id_user'] == $one->idUser) {
                    $one->send(json_encode(Message::createOfArray([
                        'operation' => self::OP_WRITEN,
                        'id_chat' => $user['id_chat'],                        
                        'autor' => $autor,
                        's_code' => $request['write'] ?? false
                    ])));
                }
            }
        }
        
        $this->controller->log('commandUserWrite -> END ', ServerController::LOG_WARNING);  
    }
    
}


