<?php
namespace console\controllers;

use Yii;
use console\daemons\EchoServer;
use yii\console\Controller;
use consik\yii2websocket\WebSocketServer;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;

use common\models\Chat;
use common\models\ChatUser;
use common\models\ChatMessage;
use common\models\ChatMessageStatus;
use common\models\ChatBlackList;
use common\models\User;
use common\models\Message;

use common\models\Master;
use yii\helpers\BaseConsole;

class ServerController extends Controller
{
    const LOG_DEBUG = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;

    public function actionStart($port = 25555)
    {
     
   
           while (true) {
        
        $server = new EchoServer();
        $server->controller = $this;
        
        if ($port) {
            $server->port = $port;           
        }
        
        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN, function($e) use($server) {
            echo "Server started at port " . $server->port . "\n";
        });
        $server->on(WebSocketServer::EVENT_WEBSOCKET_CLOSE, function($e) use($server) {
            echo "Server closed \n ";
        });
        $server->on(WebSocketServer::EVENT_CLIENT_CONNECTED, function($e) use($server) {
            echo "Client connected \n" ;
        });
        $server->on(WebSocketServer::EVENT_CLIENT_DISCONNECTED, function($e) use($server) {
            echo "Client disconnected \n" ;
        });
        $server->on(WebSocketServer::EVENT_CLIENT_ERROR, function($e) use($server) {
            echo "Client error \n" . $e;            
        });
        $server->on(WebSocketServer::EVENT_CLIENT_MESSAGE, function($e) use($server) {
            echo "Client message \n" ;
        });
        $server->on(WebSocketServer::EVENT_CLIENT_RUN_COMMAND, function($e) use($server) {
            echo "Client EVENT_CLIENT_RUN_COMMAND \n" ;
        });
        $server->on(WebSocketServer::EVENT_CLIENT_END_COMMAND, function($e) use($server) {
            echo "Client EVENT_CLIENT_END_COMMAND \n" ;
        });
        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN_ERROR, function($e) use($server) {
            echo "Client EVENT_WEBSOCKET_OPEN_ERROR \n" ;
        });        
        
        
        
        $server->start();
        
        
        }
   //     */
     //   BaseConsole::input("Enter your name \n");
        
        
        
  /*      $exit = true;
        
        while ($exit) {
            
            if (isset(\Yii::$app->db)) {
                \Yii::$app->db->close();
                \Yii::$app->db->open();
            }
            
            $command = BaseConsole::input("Enter your command \n");
            $str = '';
            switch ($command) {
                case "read" :
                    try {
                        for ($i = 3; $i < 100; $i++) {
                            if ($master = Master::find()->where(['id' => $i])->limit(1)->one()){
                                $str = serialize($master->toArray());
                                $this->log('Прочитано следующее : ' . $str);
                            }
                        }
                    } catch (\Exception $ex) {
                        $this->log("\n \n EXCEPTION : CODE = " . $ex->getCode(). ' _ TEXT = '. $ex->getMessage() . ' _ TRACE = ' . $ex->getTraceAsString());
                    }
                    break;
                case "name" : 
                    for($r = 0; $r<50; $r++){
                        if ($this->setName('radioniv')){
                            $this->log('TRUE');  
                        } else $this->log('FALSE');  
                                              
                    }                   
                    break;    
                case "exit" : $exit = false;
                    break;
            }
            if (isset(\Yii::$app->db)) {
                \Yii::$app->db->close();
                \Yii::$app->db->open();
            }
        }        
    //    */
        return Controller::EXIT_CODE_NORMAL;
    }   
    
    
    public static function saveMessage($request)
    {
        self::log('saveMessage -> START', self::LOG_WARNING);        
        
        $newMessage = new ChatMessage();
        $newMessage->setAttributes($request);
        $newMessage->id_user = $request['id_autor'];
        $usersNames = ChatUser::find()->select(['id_user', 'username'])
                ->where(['id_chat' => $newMessage->id_chat])
                ->join('INNER JOIN', 'user', 'user.id=chat_user.id_user')
                ->with('blackList')->asArray()->all(); 
        
        if (!$newMessage->save()) {
            self::log('SAVE MESSAGE -> error!!!', self::LOG_ERROR);
            self::log('saveMessage -> END', self::LOG_WARNING);
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
            self::log('Status save -> ERROR!!!' . $ex->getMessage(), self::LOG_ERROR);
            self::log('saveMessage -> END', self::LOG_WARNING);
            return null;
        }
        $request['id'] = $newMessage->id;
        self::log('saveMessage -> END', self::LOG_WARNING);
       
        return [$usersNames, $request];
    }
    
    
    
    public static function name($name)
    {
        try {        
            self::log('commandSetName - METKA1-9', self::LOG_ERROR);
            $userId = '';
            $userId = User::find()->select(['id'])->where(['username' => $name])->scalar();
            self::log('commandSetName - METKA1-10', self::LOG_ERROR);
          
        } catch (\Exception $ex) {     
            self::log('commandSetName - EXCEPTION : ' . $ex->getMessage(), self::LOG_ERROR);
            self::log('commandSetName -> END', self::LOG_WARNING);
            return null;
        }
        
        if ($userId) return $userId;
        else return null;       
    }
    
    
    
    public static function log(string $message, int $code = self::LOG_DEBUG){
        switch ($code) {
           
            case self::LOG_DEBUG : echo $message . " \n";
             
                break;
            case self::LOG_WARNING : echo $message . " \n", Console::FG_YELLOW;
                
                break;
            case self::LOG_ERROR : echo $message . " \n", Console::FG_RED;
               
                break;
        }
    }
    
    public function signalHandler()
    {
        
    }
}


