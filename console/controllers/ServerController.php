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

use React\Socket\Server;
use React\Socket\SecureServer;
use React\EventLoop\Factory;


class ServerController extends Controller
{
    const LOG_DEBUG = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;
    
    public $start = true;

    public function actionStart($port = 25555)
    {
        
        
    //    $flag = TRUE;
    //    while ($flag) {
        
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
            echo "Client error \n";
            echo '$e->getCode() => ' . $e->exception->getCode() . "\n";
            echo '$e->getFile() => ' . $e->exception->getFile() . "\n";
            echo '$e->getLine() => ' . $e->exception->getLine() . "\n";
            echo '$e->getMessage() => ' . $e->exception->getMessage() . "\n";
            echo '$e->getPrevious() => ' . $e->exception->getPrevious() . "\n";
            echo '$e->getTraceAsString() => ' . $e->exception->getTraceAsString() . "\n";           
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
            
            echo '$e->getCode() => ' . $e->exception->getCode() . "\n";
            echo '$e->getFile() => ' . $e->exception->getFile() . "\n";
            echo '$e->getLine() => ' . $e->exception->getLine() . "\n";
            echo '$e->getMessage() => ' . $e->exception->getMessage() . "\n";
            echo '$e->getPrevious() => ' . $e->exception->getPrevious() . "\n";
            echo '$e->getTraceAsString() => ' . $e->exception->getTraceAsString() . "\n";  
        });  
        
        $server->start();
        
       /* $command = BaseConsole::input();
        switch ($command) {
            case 'stop': 
                $flag = FALSE;
                $server->stop();
                break;
        }*/
        
   //     }
        return Controller::EXIT_CODE_NORMAL;
    }   
    
    
    public function log(string $message, int $code = self::LOG_DEBUG){
        switch ($code) {           
            case self::LOG_DEBUG : $this->stdout($message . "\n");             
                break;
            case self::LOG_WARNING : $this->stdout($message . "\n", Console::FG_YELLOW);                
                break;
            case self::LOG_ERROR : $this->stdout($message . "\n", Console::FG_RED);               
                break;
        }
    }    
    
}


