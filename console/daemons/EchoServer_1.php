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

use common\models\Master;
use common\models\Manager;
use common\models\Klient;

use yii\db\Query;

use console\controllers\ServerController;
use yii\helpers\Json;

use common\models\AuthItem;
use common\models\AuthAssignment;
use yii\helpers\ArrayHelper;


class EchoServer_1 extends WebSocketServer
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
    const OP_SYSTEM = 108;
    const OP_ERROR_NAME = 109;
    const OP_SEARCH_USER = 110;
    const OP_GET_CHATS = 111;
    const OP_GET_HISTORY_MESSAGE = 112;
    const OP_EXIT_CHAT = 113;
    const OP_REMOVE_USER = 114;
    const OP_ADD_USER = 115;
    const OP_REMOVE_CHAT = 116;
    const OP_BLOCK_USERS = 117;
    const OP_UNLOOCK_USERS = 118;
    const OP_BLACK_LIST_USERS = 119;
    
    
    const ZAKAZ_AKTIVATE = 150;
    const ZAKAZ_DIAKTIVATE = 151;
   
    const NULL_MESSAGES = 1008;    
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
    
    /**
     * Обработчик системных сообщений
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */    
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
            $message->id_chat = ($chatId != NULL) ? $chatId : $chat->id;
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
            $this->controller->log('commandSystem -> ERROR MODEL -> ' . '$message->id_chat=>'.$message->id_chat
                    .', $message->id_user=>'.$message->id_user 
                    .', $message->message=>'.$message->message
                    .', $message->date=>'. $message->date
                    .', $message->time=>' . $message->time, ServerController::LOG_ERROR);
            try {               
                if ($message->validate() && $message->save()) {

                    $send = ChatMessageStatus::MESSAGE_SEND;
                    foreach ($this->clients as $one) {
                        if ($one->idUser == $managerId) {
                            
                            if (!$chatId) {
                                $one->send(json_encode(Message::createOfArray([ 'operation' => self::OP_CREATE_NEW_CHAT, 
                                    'status' => self::STATUS_ACCEPT, 'id_chat' => $chat->id, 'id_autor' => 0, 
                                    'id_user' => $managerId, 'message' => $chat->alias
                                ])));
                            }
                            
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
                    $this->controller->log('$message => '. $message->errors, ServerController::LOG_ERROR); 
                    $this->controller->log('_SOOBSHENIE NE SOXRANENO_', ServerController::LOG_ERROR); 
                }
            } catch (\Exception $ex) {          
                $this->controller->log('commandSystem -> ERROR $ex = ' . $ex->getMessage(), ServerController::LOG_ERROR); 
                $this->controller->log('commandSystem -> END', ServerController::LOG_WARNING); 
            }
        }
        $this->controller->log('commandSystem -> END', ServerController::LOG_WARNING);        
    }
    
    
    /**
     * Проверяет новое сообщение
     * @param ConnectionInterface $client
     * @param string $msg
     * @return type
     */
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
    

    /**
     * Сохраняет новое сообщение в бд
     * @param array $request
     * @return type
     */
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
    
    
    /**
     * Отправляет новое сообщение всем активным пользователям
     * @param array $usersNames
     * @param Message $newMessage
     */
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
    
    
    /**
     * Работает при создании нового сообщения
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
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
                  //      $this->controller->log('METKA_5');
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
    

    /**
     * Записывает новый статус сообщения для определенного клиента в бд
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
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
    

    /**
     * Пингует канал
     * @param ConnectionInterface $client
     * @param type $msg
     */
    public function commandPing(ConnectionInterface $client, $msg)
    {   
        $this->controller->log('PING', ServerController::LOG_WARNING);
        
        $client->send( json_encode('pong') );        
    }
    
    
    /**
     * Возвращает список пользователей чата
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
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
            $client->send(json_encode(['operation' => self::OP_LIST_USERS, 'blocked' => $blocked, '$roles' => $roles,
                        'users' => $users, 'status' => self::STATUS_ERROR, 's_message' => 'Ошибка поиска в бд']));
            $this->controller->log('commandListChatUsers EROR Ошибка поиска в бд', ServerController::LOG_ERROR);
            $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
            return;            
        }
        
        $client->send(json_encode(['operation' => self::OP_LIST_USERS, 'status' => self::STATUS_ACCEPT, 'chat_users' => $roles, 'black_list' => $blocked, 'id' => $client->idUser, 'autor' => $users[key($users)]['autor']]));
        
        $this->controller->log('commandListChatUsers -> END', ServerController::LOG_WARNING);
    }
        
    
    /**
     * Создает новый чат
     * @param ConnectionInterface $client
     * @param type $msg
     */
    public function commandCreateChat(ConnectionInterface $client, $msg)
    {       
        $this->controller->log('commandCreateChat -> START', ServerController::LOG_WARNING);
        
        if (!$this->securityUser($client)) { return; }  
        
        $request = json_decode($msg, true);                 
        if (!(isset($request['chat_name']) && $name = trim($request['chat_name']))){    
            $this->controller->log('commandCreateChat ERROR => USERS NULL', ServerController::LOG_ERROR);
            return ; 
        }
        if (!(isset($request['users']) && count($request['users']) > 0)){
            $this->controller->log('commandCreateChat ERROR => USERS NULL', ServerController::LOG_ERROR);
            return;           
        }
        if (strlen($name) > 30) $name = substr($name, 0, 30);
        
        $chat = new Chat(['autor' => $client->idUser, 'create_at' => date('Y-m-d'), 'status' => Chat::CHAT_ACTIVE, 'alias' => $name]);
     
        $request['users'][] = $chat->autor;
        if ($chat->save()) {
            foreach($request['users'] as $one) {
                $chatUser = new ChatUser(['id_chat' => $chat->id, 'id_user' => $one]);
             
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
    }
    
    
    /**
     * Обработчик события печатания участника чата
     * @param ConnectionInterface $client
     * @param type $msg
     */
    public function commandUserWrite(ConnectionInterface $client, $msg)
    {        
        $this->controller->log('commandUserWrite -> START ', ServerController::LOG_WARNING);   
        
        if (!$this->securityUser($client)) { return; }
        
        $request = json_decode($msg, true);  
       
            // $this->controller->log('$request["id_chat"] -> ' . $request['id_chat'], ServerController::LOG_WARNING);   
        
        if (empty($request['id_chat']) && trim($request['id_chat']) != NULL){
            $this->controller->log('commandUserWrite -> ERROR!!! id_chat=NULL', ServerController::LOG_ERROR);   
            return;
        }
        
        try {
            $users = ChatUser::find()->select(['id_chat', 'id_user', 'username'])
                    ->where(['id_chat' => $request['id_chat']])
                    ->join('INNER JOIN', 'user', 'user.id=chat_user.id_user')->asArray()->all();
        } catch (\Exception $ex) {
            $this->controller->log('commandUserWrite -> ERROR!!!' . $ex->getMessage(), ServerController::LOG_ERROR);   
        }
        
      /*  foreach ($users as $user){
            if ($user['id_user'] == $client->idUser) $autor = $user['username'];
        }*/
        
        foreach ($users as $user){
            if ($user['id_user'] == $client->idUser) continue;
            
            foreach ($this->clients as $one){
                if ($user['id_user'] == $one->idUser) {
                    $one->send(json_encode(Message::createOfArray([
                        'operation' => self::OP_WRITEN,
                        'id_chat' => $user['id_chat'],                        
                        'autor' => $client->name,// $autor,
                        's_code' => $request['write'] ?? false
                    ])));
                }
            }
        }
        
        $this->controller->log('commandUserWrite -> END ', ServerController::LOG_WARNING);  
    }
    
        
    /**
     * Инициализирует клиента
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandSetName(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandSetName -> START', ServerController::LOG_WARNING);
        $request = json_decode($msg, true);    

        if (empty($request['name']) || !$name = trim($request['name'])) { 
            $this->controller->log('request = ' . $msg);
            $this->controller->log('commandSetName - ERROR NAME NULL', ServerController::LOG_ERROR);            
            $this->controller->log('commandSetName -> END', ServerController::LOG_WARNING);       
            return;
        }
        
        $client->name = $name;
        if (!$client->idUser = $this->securityUser($client)) {  
            $client->name = NULL;
        }
        
      /*  $this->securityStatus(['id_user' => $client->idUser, 
                's_code' => ChatMessageStatus::MESSAGE_DELIVERED], TRUE, $client);   
        */
        $this->controller->log('commandSetName -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Возвращает инициализационные данные клиента
     * @param ConnectionInterface $client
     * @param type $msg
     */
    public function commandNameClient(ConnectionInterface $client, $msg)
    {
        $client->send(json_encode(['operation'=> 'name', 'name' => $client->name, 'id_user' => $client->idUser]));
    }
    
    
    /**
     * Ищет пользователей в БД при создании нового чата
     * @return type
     */
    public function commandSearchUser(ConnectionInterface $client, $msg)
    {    
        $this->controller->log('commandSearchUser -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return; 
           
        $request = json_decode($msg, true); 
        $search = $request['search'];
        
        if (count($search) > 0) { 
            $result = [];            
            $where = 'username LIKE "%' . $search['username'] . '%" AND phone LIKE "%' . $search['phone'] 
                    . '%" AND familiya LIKE "%' . $search['familiya'] 
                    . '%" AND imya LIKE "%' . $search['imya'] 
                    . '%" AND otchestvo LIKE "%' . $search['otchestvo'] . '%"';
            $where1 = 'username LIKE "%' . $search['username'] 
                    . '%" AND (phone1 LIKE "%' . $search['phone'] 
                    . '%" OR phone2 LIKE "%' . $search['phone'] 
                    . '%" OR phone3 LIKE "%' . $search['phone'] 
                    . '%") AND familiya LIKE "%' . $search['familiya'] 
                    . '%" AND imya LIKE "%' . $search['imya'] 
                    . '%" AND otchestvo LIKE "%' . $search['otchestvo'] . '%"';           

            $listUsers = Master::find()->select(['username', 'id' => 'id_master', 'familiya', 'imya', 'otchestvo'])
                    ->where($where)->join('INNER JOIN', 'user', 'user.id=master.id_master')
                    ->asArray()->all();          
            foreach ($listUsers as $one) $result[] = $one;
          
            $listUsers = Manager::find()->select(['username', 'id' => 'id_manager', 'familiya', 'imya', 'otchestvo'])
                    ->where($where1)->join('INNER JOIN', 'user', 'user.id=manager.id_manager')
                    ->asArray()->all();
            foreach ($listUsers as $one) $result[] = $one;
          
            $listUsers = Klient::find()->select(['username', 'id' => 'id_klient', 'familiya', 'imya', 'otchestvo'])
                    ->where($where)->join('INNER JOIN', 'user', 'user.id=klient.id_klient')
                    ->asArray()->all();
            foreach ($listUsers as $one) $result[] = $one;
         
            
            if (count($result) > 0) {
                $client->send (json_encode(["status" => self::STATUS_ACCEPT, 'operation' => self::OP_SEARCH_USER,
                                        "users" => $result, 'id' => $client->idUser]));
                $this->controller->log('commandSearchUser -> END', ServerController::LOG_WARNING);
                return;
            }
        } 
        
        $client->send (json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не найдено совпадений']));
        $this->controller->log('commandSearchUser -> END', ServerController::LOG_ERROR);         
    }
    
    
    /**
     * Возвращает список всех доступных чатов для пользователя
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandGetChats(ConnectionInterface $client, $msg)
    {            
       /* $blackList = ChatBlackList::find()->select(['locked'])
                ->where(['blocking' => $client->idUser])->asArray()->all();
        
        $blocked = '';
        foreach ($blackList as $one) {
            $blocked = $blocked . $one['locked'] . ', ';
        }
        if ($blocked != '') { 
            $blocked = ' AND u.id_user IN (' . substr ($blocked, 0, strlen ($blocked)-2) . ')';
        }
        */
        
        $this->controller->log('commandGetChat -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return; 
        
        try {
            $chats = Yii::$app->db->createCommand('SELECT id, autor, alias, status FROM chat c, chat_user u '
                    . ' WHERE c.id=u.id_chat AND c.status <> "' . Chat::CHAT_DELETED . '" AND u.id_user=' 
                    . $client->idUser)->queryAll();
            
            if (count($chats) == 0){                
                $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'chats' => []]));
                $this->controller->log('commandGetChat1 -> END', ServerController::LOG_ERROR);
                return;
            }
            $idChats = ArrayHelper::getColumn($chats, 'id');    
            
            $messages = ChatMessage::findBySql('SELECT * FROM chat_message WHERE (id_chat, date, time) in 
                    (
                    select id_chat, date, MAX(time) from chat_message c
                    where (id_chat, date) in
                        (
                        select id_chat, max(date)
                        from chat_message WHERE id_chat IN ('. implode(', ', $idChats) .')
                        group by id_chat
                        )
                    GROUP BY id_chat
                    )')
                    ->indexBy('id_chat')
                    ->asArray()->all();
          
            $massUs = ArrayHelper::getColumn($messages, 'id_user');            
            $users = User::find()->select(['id', 'username'])->where(['id' => $massUs])->indexBy('id')->asArray()->all();
                        
        } catch (\Exception $ex) {
            $client->send(json_encode(["status" => self::STATUS_ERROR, "s_message" => 'Ошибка поиска в бд']));
            $this->controller->log('commandGetChat2 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_GET_CHATS, 'chats' => $chats, 'messages' => $messages,
                'users' => $users, 'user' => $client->idUser]));             
        $this->controller->log('commandGetChat -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Команда разблокирования пользователя
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandUnlockUsers(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandUnlockUsers -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return; 
        
        $request = json_decode($msg, true);
        
        if (!$idUsers = $request['users']) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandUnlockUsers1 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        if (!count($idUsers) > 0) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не выбран ни один пользователь']));
            $this->controller->log('commandUnlockUsers2 -> END', ServerController::LOG_ERROR);
            return;
        }        
        
        foreach ($idUsers as $one) {
            $model = ChatBlackList::find()->where(['blocking' => $client->idUser, 'locked' => $one])->one();
            try {
                if ($model) $model->delete();
            } catch (\Exception $ex) { 
                $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка при разблокировании']));
                $this->controller->log('commandUnlockUsers3 -> END', ServerController::LOG_ERROR);
                return;
            }
        }
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_UNLOOCK_USERS,
                'users' => $request['users'], 'blackList' => $request['blackList'], 's_message' => 'Пользователи разблокированны']));
        $this->controller->log('commandUnlockUsers -> END', ServerController::LOG_WARNING);
    }

    
    /**
     * Команда блокирования пользователя  
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type   * 
     */
    public function commandBlockUsers(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandBlockUsers -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        if (!$idUsers = $request['users']) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandBlockUsers1 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        if (!count($idUsers) > 0) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не выбран ни один пользователь']));
            $this->controller->log('commandBlockUsers2 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        foreach ($idUsers as $one){
            if ($one == $client->idUser) {
                $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не можете заблокировать самого себя']));
                $this->controller->log('commandBlockUsers3 -> END', ServerController::LOG_ERROR);
                return;
            }
        }
        
        $date = date('Y-m-d');
        $time = date('H:i:s');
        try {
            foreach ($idUsers as $one) {
                Yii::$app->db->createCommand()->upsert('chat_black_list', 
                        ['blocking' => $client->idUser, 'locked' => $one, 'date' => $date, 'time' => $time])->execute();
            }
        } catch (Exception $ex) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных']));
            $this->controller->log('commandBlockUsers4 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_BLOCK_USERS,
            'users' => $request['users'], 's_message' => 'Пользователи успешно добавлены в черный список']));
        $this->controller->log('commandBlockUsers -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Функция возвращает историю сообщений на последнюю дату в чате
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandHistoryChat(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandHistoryMessage -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        $id = $request['id'] ?? null;
        if (!$id) {
            $client->send(json_encode(["status" => self::STATUS_ERROR, 'operation' => self::OP_GET_HISTORY_MESSAGE,
                                "s_message" => "Неверные входные данные"]));
            $this->controller->log('commandHistoryMessage1 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $date = $request['date'] ?? null;        
        if ($date != null) {
            $date = ChatMessage::find()->select(['date'])->where(['id_chat' => $id])
                    ->andWhere('date <"' . $date . '"')->orderBy(['date' => SORT_DESC])->scalar();
            if ($date == null) {
                $client->send(json_encode(["status" => self::STATUS_ERROR, 'operation' => self::OP_GET_HISTORY_MESSAGE,
                                    "s_code" => self::NULL_MESSAGES, "s_message" => "Больше нет сообщений"]));
                $this->controller->log('commandHistoryMessage2 -> END', ServerController::LOG_ERROR);
                return;
            }
            $date = ['chat_message.date' => $date];
            $orderBy = ['chat_message.time' => SORT_DESC];
        } else { 
            $date = ' chat_message.date IN (SELECT MAX(date) FROM chat_message WHERE id_chat='. $id .')';
            $orderBy = ['chat_message.time' => SORT_ASC];
        }
        
        $update = Yii::$app->db->createCommand('SELECT ms.id_message FROM chat_message_status ms '
                . 'LEFT JOIN chat_message m on m.id=ms.id_message where ms.id_user=' . $client->idUser
                . ' AND m.id_chat='. $id .' AND ms.id_user <> m.id_user AND status_message <> "'
                . ChatMessageStatus::MESSAGE_READED .'"')->queryAll();
        
        $update = ArrayHelper::getColumn($update, 'id_message');
        if (count($update) > 0) { 
            ChatMessageStatus::updateAll(['status_message' => ChatMessageStatus::MESSAGE_READED],
                ['id_message' => $update,
                    'id_user' => $client->idUser]);
        }
       
        $model = ChatMessage::find()->select([
                'id' => 'chat_message.id', 'id_chat', 'id_user' => 'chat_message.id_user',
                'parent_id', 'message', 'file', 'date' => 'chat_message.date',
                'time' => 'chat_message.time', 'autor' => 'user.username', 'status_message'
            ])->where(['id_chat' => $id])
                ->andWhere($date)
                ->orderBy($orderBy)
                ->join('INNER JOIN', 'user', 'chat_message.id_user=user.id')
                ->join('INNER JOIN', 'chat_message_status', 
                        'chat_message_status.id_message=chat_message.id '
                        . 'AND chat_message_status.id_user=chat_message.id_user')
                ->asArray()->all();
     
        $client->send(json_encode(["status" => self::STATUS_ACCEPT, 'operation' => self::OP_GET_HISTORY_MESSAGE,
                    "chat" => $model, "id" => $client->idUser, 'loadingData' => $request['loadingData']]));
        $this->controller->log('commandHistoryMessage -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Функцияя отвечает за удаление чата
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandRemoveChat(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandRemoveChat -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        if (!($id = $request['id'])) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandRemoveChat1 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $model = Chat::find()->where('id=:id', [':id' => $id])->one();
        
        if (!$model) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Чат не найден']));
            $this->controller->log('commandRemoveChat2 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        if ($model->autor != $client->idUser) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 'operation' => self::OP_REMOVE_CHAT,
                        's_message' => 'Удаление не возможно, вы не являетесь автором чата']));
            $this->controller->log('commandRemoveChat3 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $model->status = Chat::CHAT_DELETED;
        if ($model->save()) {
            $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_REMOVE_CHAT, 
                        'id' => $model->id, 's_message' => 'Чат удален']));
            $this->controller->log('commandRemoveChat -> END', ServerController::LOG_WARNING);
            return;
        }    
        
        $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных, повторите операцию']));
        $this->controller->log('commandRemoveChat4 -> END', ServerController::LOG_ERROR);
    }

    
    /**
     * Функция отвечает за выход из чата
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandExitChat(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandExitChat -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        if (!$id = $request['id']) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandExitChat1 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        if(!$user = ChatUser::findOne(['id_chat' => $id, 'id_user' => $client->idUser])) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не участвуете в этом чате']));
            $this->controller->log('commandExitChat2 -> END', ServerController::LOG_ERROR);
            return;            
        }
        
        $listUsers = ChatUser::find()->where('id_chat=' . $id . ' AND id_user <> ' . $client->idUser)->asArray()->all();
        
        $idNewAutor = $request['id_user'] ?? null;
        $chat = Chat::findOne(['id' => $id]);
        
        if (count($listUsers) == 1){
            $chat->status = Chat::CHAT_DELETED;
            if ($chat->save()){
                $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_EXIT_CHAT, 's_message' => 'В чате всего два пользователя, вследстии чего чат удален']));
                $this->controller->log('commandExitChat3 -> END', ServerController::LOG_WARNING);
                return;
            } else {
                $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных, повторите операцию']));
                $this->controller->log('commandExitChat4 -> END', ServerController::LOG_ERROR);
                return;
            }
        }
        
        if ($chat->autor == $client->idUser) {
            if ($idNewAutor) {
                $chat->autor = $idNewAutor;
            } else {
                $chat->autor = $listUsers[0]['id_user'];
            }
            if (!$chat->save()){
                $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Операция прервана, повторите']));
                $this->controller->log('commandExitChat5 -> END', ServerController::LOG_ERROR);
                return;                
            }              
        } 
        
        if (!$user->delete()) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Операция прервана, повторите1']));
            $this->controller->log('commandExitChat6 -> END', ServerController::LOG_ERROR);
            return; 
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_EXIT_CHAT,
                        's_message' => 'Операция выполнена']));
        $this->controller->log('commandExitChat -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Функция удаления из чата определенного пользователя (только для автора чата)
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandDeleteUserFromChat(ConnectionInterface $client, $msg)
    {      
        $this->controller->log('commandDeleteUserFromChat -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        if (!$id = $request['id']) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandDeleteUserFromChat1 -> END', ServerController::LOG_ERROR);
            return;
        }
        if (Chat::find()->select(['autor'])->where(['id' => $id])->scalar() != $client->idUser) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не являетесь автором чата']));
            $this->controller->log('commandDeleteUserFromChat2 -> END', ServerController::LOG_ERROR);
            return;
        }
        if (ChatUser::find()->where(['id_chat' => $id])->count() <= 2) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Удаление не возможно. В чате всего два пользователя, удалите чат целиком']));
            $this->controller->log('commandDeleteUserFromChat3 -> END', ServerController::LOG_ERROR);
            return;            
        }
        if (!$users = $request['users']) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error1']));
            $this->controller->log('commandDeleteUserFromChat4 -> END', ServerController::LOG_ERROR);
            return;
        }   
        
        try {
            foreach ($users as $one) {
                $us = ChatUser::find()->where(['id_chat' => $id, 'id_user' => $one])->limit(1)->one();
                if (!$us->delete()) {
                    $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Некоторые пользователи не были удалены']));
                    $this->controller->log('commandDeleteUserFromChat5 -> END', ServerController::LOG_ERROR);
                    return;
                }
            }
        } catch (\Exception $ex) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных']));
            $this->controller->log('commandDeleteUserFromChat6 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_REMOVE_USER, 
                        's_message' => 'Пользователи успешно удалены'])); 
        $this->controller->log('commandDeleteUserFromChat -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Функция присоединения к чату нового пользователя (только для автора)
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandAddUserInChat(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandAddUser -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;  
        
        $request = json_decode($msg, true);
        
        if (!($id = $request['id']) || !($users = $request['users']) || !(count($users) > 0)) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
            $this->controller->log('commandAddUser1 -> END', ServerController::LOG_ERROR);
            return;
        }
       // $this->controller->log('$id = ' . $id, ServerController::LOG_WARNING);
      //  $this->controller->log(Chat::find()->select(['autor'])->where(['id' => $id])->scalar() . ' != ' . $client->idUser, ServerController::LOG_WARNING);
        
        if (Chat::find()->select(['autor'])->where(['id' => $id])->scalar() != $client->idUser) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не являетесь автором чата']));
            $this->controller->log('commandAddUser2 -> END', ServerController::LOG_ERROR);
            return;
        }
  
        foreach ($users as $one) {
            $user = User::find()->select(['id'])->where(['id' => $one])->limit(1)->one();            
            if (!$user) {
                $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']));
                $this->controller->log('commandAddUser3 -> END', ServerController::LOG_ERROR);
                return;
            }
        }
        
        try {
            foreach ($users as $one) {
                if (!ChatUser::find()->where(['id_chat' => $id, 'id_user' => $one])->limit(1)->asArray()->one()) {
                    Yii::$app->db->createCommand()
                        ->insert('chat_user', ['id_chat' => $id, 'id_user' => $one])->execute();
                }
            }
        } catch (\Exception $ex) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка записи в базу данных']));
            $this->controller->log('commandAddUser4 -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_ADD_USER,
                                's_message' => 'Пользователи успешно добавлены в чат']));
        $this->controller->log('commandAddUser -> END', ServerController::LOG_WARNING);
    }
    
    
    /**
     * Функция возвращает черный список пользователей
     * @param ConnectionInterface $client
     * @param type $msg
     * @return type
     */
    public function commandBlackListUsers(ConnectionInterface $client, $msg)
    {
        $this->controller->log('commandBlackListUsers -> START', ServerController::LOG_WARNING);
        if (!$this->securityUser($client)) return;                                      //  проверяем пользователя
        
        $lockUsers = ChatBlackList::find()->select(['locked'])
                ->where(['blocking' => $client->idUser])->asArray()->all();             //  выбираем всех заблокированных пользователей
        
        if (!$lockUsers){                                                               //  проверяем наличие заблокированных
            $client->send(json_encode(['status' => self::STATUS_ACCEPT, 
                            'users' => [], 's_message' => 'Черный список пуст']));
            $this->controller->log('commandBlackListUsers -> END', ServerController::LOG_WARNING);
            return;
        }
        
        $roles = AuthAssignment::find()
                ->where(['user_id' => ArrayHelper::getColumn($lockUsers, 'locked')])
                ->asArray()->all();                                                     //  читаем роли пользователей, 
                                                                                        //          необходимо для реализации костыля таблиц по ролям
        try {
            foreach ($roles as $key => $one){
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
                    default: return $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не опознанный пользователь!!!']));
                }
                $query = Yii::$app->db->createCommand('SELECT username, CONCAT(p.familiya, " ", p.imya, " ", p.otchestvo) AS fio '
                        . 'FROM '. $table .' p, user u WHERE p.'. $field .'=u.id AND u.id=' . $one['user_id'])->queryOne();
                                                                                        //  вытаскиваем данные пользователя из бд
                $roles[$key]['fio'] = $query['fio'];
                $roles[$key]['username'] = $query['username']; 
            }
        } catch (\Exception $ex) {
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка поиска в бд']));
            $this->controller->log('commandBlackListUsers -> END', ServerController::LOG_ERROR);
            return;
        }
        
        $client->send(json_encode(['status' => self::STATUS_ACCEPT, 'operation' => self::OP_BLACK_LIST_USERS, 'users' => $roles]));
        $this->controller->log('commandBlackListUsers => START', ServerController::LOG_WARNING);
    }       
    
    
    /**
     * Проверяет активного клиента перед выполнением команды
     * @param ConnectionInterface $client
     * @return type
     */
    protected function securityUser(ConnectionInterface $client)
    {
        if (!isset($client->name)) {
            $this->controller->log('SECURITY USER ERROR => NULL NAME', ServerController::LOG_ERROR);
            $client->send(json_encode(['status' => self::STATUS_ERROR, 's_code' => self::ERROR_USER_NAME]));
            return NULL;
        } 
        
        $userId = User::find()->select(['id'])->where(['username' => $client->name])->scalar();  
        
        if ((isset($client->idUser) && $userId == $client->idUser) || $userId) {            
            return $userId;
        } 
        
        $this->controller->log('SECURITY USER ERROR => ID ERROR', ServerController::LOG_ERROR);
        $client->send(json_encode(['status' => self::STATUS_ERROR, 's_code' => self::ERROR_USER_NAME]));
        return NULL;
    }
    
}


