<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/13
 * Time: 下午1:18
 */

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\httpClient;

class server_9503
{
    public $server;

    private $data;
    
    public function __construct($config,$data)
    {
        //全局对象
        $this->server = new \swoole\websocket\server('0.0.0.0',9503);
        $this->data = $data;
        $this->server->set($config);
        
        //注册事件
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('message',[$this,'onMessage']);
        //全局生命周期
        $this->server->start();
    }

    //工作进程启动
    public function onWorkerStart($server,$worker_id)
    {
        //去注册中心 注册当前服务 只注册一次
        if ($worker_id == 0) {
            $data = $this->data;
            $data['method'] = 'register';//代表当前是注册服务的方法

            //发送请求 注册中心
            $websocket = new httpClient('127.0.0.1',9800);

            //异步websocket请求
            $websocket->async_websocket(function ($cli) use ($data) {
                $cli->push(json_encode($data));//发送一个消息去注册中心
            });
        }

    }

    public function onMessage($server)
    {
        
    }

}

$config = [
    'worker_num' => 2, //工作进程数量
    'package_max_length' => 1024*1024*10,
    'max_request' => 3000,//最大请求数量,防止内存爆掉
];


//购物车服务
$data = [
    'ip' => '127.0.0.1',
    'port' => 9503,
    'serviceName' => 'CartService'
];

new server_9503($config,$data);