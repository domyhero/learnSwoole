<?php

/**
 * 列表服务端
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 9:13
 */

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\httpClient;
class ListServer
{
    private $serv;
    private $data;
    function __construct($config,$connect_config)
    {
        $this->serv = new swoole_websocket_server('0.0.0.0',9504);
        $this->serv->set($config);
        $this->data = $connect_config;
        $this->serv->on('open', [$this, 'onOpen']);
        $this->serv->on('workerStart', [$this, 'onWorkerStart']);
        $this->serv->on('message', [$this, 'onMessage']);
        $this->serv->start();
    }

    public function onWorkerStart($serv,$worker_id)
    {
        //进行服务注册
        if ($worker_id == 0) {
            $data = $this->data;
            $data['method'] = 'register';

            $webSocketCli = new httpClient('127.0.0.1',9501);
            $webSocketCli->asyncWebsocket(function ($cli) use ($data) {
               $cli->push(json_encode($data));
               swoole_timer_tick(2000, function ($id) use ($cli) {
                   $cli->push('',9);
               });
            });
        }
    }

    public function onOpen($serv,$request)
    {
        
    }

    public function onMessage($serv,$frame)
    {

    }

}

$config = [
    'worker_num' => 2,
    'package_max_length' => 1024*1024*10,
];

$connect_config = [
    'ip' => '127.0.0.1',
    'port' => '9504',
    'serverName' => 'ListServer'
];

new ListServer($config,$connect_config);