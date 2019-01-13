<?php
namespace console\controllers;


use yii\console\Controller;
use yii\helpers\BaseConsole;
use Workerman\Worker;
use yii\helpers\Console;

class SerController extends Controller
{
    const LOG_DEBUG = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;
    
    const COMMAND_EXIT = 'exit';
    const COMMAND_STOP_SERVER = 'stop';
    const COMMAND_RUN_SERVER = 'run';

    public $base;
    
    public function actionStart($port = 25555)
    {
        
        // Create a Websocket server
        $ws_worker = new Worker("websocket://0.0.0.0:" . $port);

        // 4 processes
        $ws_worker->count = 1;

        // Emitted when new connection come
        $ws_worker->onConnect = function($connection)
        {
            echo "New connection\n";
         };

        // Emitted when data received
        $ws_worker->onMessage = function($connection, $data)
        {
            // Send hello $data
            $connection->send('hello ' . $data);
        };

        // Emitted when connection closed
        $ws_worker->onClose = function($connection)
        {
            echo "Connection closed\n";
        };

        $ws_worker->
        // Run worker
   //     Worker::runAll();
        Worker::
        
     /*   $str = '';
        $end = true;
        while ($end) {
            $str = BaseConsole::input("Enter command -> ");
            switch ($str){
                case SerController::COMMAND_EXIT: $end = false;
                    break;
                case SerController::COMMAND_STOP_SERVER: $this->log('Server stop');
                    break;
                case SerController::COMMAND_RUN_SERVER: $this->log('Server run');
                    break;
            }            
        }
        */
        return Controller::EXIT_CODE_NORMAL;
    } 

    public function log(string $message, int $code = self::LOG_WARNING){
        switch ($code) {
            case self::LOG_DEBUG : $this->stdout($message . " \n");
                break;
            case self::LOG_WARNING : $this->stdout($message . " \n", Console::FG_YELLOW);
                break;
            case self::LOG_ERROR : $this->stdout($message . " \n", Console::FG_RED);
                break;
        }
    }
    
    
}


