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
        $app = new \Ratchet\Http\HttpServer(
            new \Ratchet\WebSocket\WsServer(
                new EchoServer()
            )
        );

        $loop = \React\EventLoop\Factory::create();

        $secure_websockets = new \React\Socket\Server($loop);
        $secure_websockets = new \React\Socket\SecureServer($secure_websockets, $loop, [
            'local_cert' => '/etc/letsencrypt/live/gradinas.ru/fullchain.pem',
            'local_pk' => '/etc/letsencrypt/live/gradinas.ru/privkey.pem',
            'verify_peer' => false
        ]);
        
        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN, function($e) use($server) {
            echo "Server started at port " . $server->port .  "\n";           
        });
        
        $secure_websockets_server = new \Ratchet\Server\IoServer($app, $secure_websockets, $loop);
        $secure_websockets_server->run();
    }
    
}


