<?php
namespace console\controllers;

use Yii;
use console\daemons\EchoServer;
use yii\console\Controller;
use yii\helpers\Console;

use Workerman\Worker;


class ServerController extends Controller
{
    const LOG_DEBUG = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;
    
    public $start = true;

    public function actionStart()
    {     
        require_once __DIR__ . '/../../vendor/autoload.php';
     /*   $context = array(
            'ssl' => array(
                'local_cert'  => '/etc/letsencrypt/live/gradinas.ru/fullchain.pem',
                'local_pk'    => '/etc/letsencrypt/live/gradinas.ru/privkey.pem',
                'verify_peer' => false,
            )
        );*/

        // Create a Websocket server with ssl context.
        $ws_worker = new Worker("websocket://0.0.0.0:25555"/*, $context*/ );

        // Enable SSL. WebSocket+SSL means that Secure WebSocket
        // 
        // (wss://). 
        // The similar approaches for Https etc.
    //    $ws_worker->transport = 'ssl';

        // 4 processes
        $ws_worker->count = 4;

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

        // Run worker
        Worker::runAll();
        return Controller::EXIT_CODE_NORMAL;
    }   
    
    /*
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
    */
}


