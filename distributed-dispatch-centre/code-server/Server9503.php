<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/16
 * Time: 下午9:37
 */

require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\httpClient;

class Server9503
{
    public $serv;
    public $connect_config;

    public function __construct($config,$connect_config)
    {
        $this->connect_config = $connect_config;
        $this->serv = new \swoole\websocket\server('0.0.0.0',9503);
        $this->serv->set($config);

        $this->serv->on('workerStart',[$this,'onWorkerStart']);
        $this->serv->on('message',[$this,'onMessage']);
        $this->serv->on('close',[$this,'onClose']);

        $this->serv->start();
    }

    //工作进程启动
    public function onWorkerStart($serv,$worker_id)
    {
        //当工作进程启动时 去服务中心注册服务 这里只注册一次
        if ($worker_id == 0) {
            //像RPC中心进行注册
            //进行请求消息 组装
            $data =$this->connect_config;
            $data['method'] = 'register';//代表注册方法

            $websocket = new httpClient('127.0.0.1',9501);
            $websocket->async_websocket(function ($cli) use ($data) {
                $cli->push(json_encode($data));

                //与客户端建立连接心跳
                swoole_timer_tick(3000, function ($id) use ($cli) {
                    $cli->push('',9);//发送ping包 ,不会触发客户端的ommessage事件
                });


            });
        }

    }

    //接收到客户端消息时触发
    public function onMessage($serv,$frame)
    {

    }

    //当服务器主动关闭,或自己关闭触发

    public function onClose($serv,$fd,$reactorId)
    {
        common::dump('我关闭了');
    }


}

$config = [
    'worker_num' => 2,//开启两个工作进程
    'max_request' => 3000,//最大连接数为3000
    'package_max_num' => 1024*1024*10,//传输最大10兆
];
$connect_config = [
    'ip' => '127.0.0.1',//当前请求的地址
    'port' => 9503,//当前的端口
    'serverName' => 'CartService',
];

new Server9503($config,$connect_config);