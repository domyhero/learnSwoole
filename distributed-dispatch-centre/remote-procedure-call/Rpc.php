<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/13
 * Time: 下午8:01
 */
require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
class Rpc
{

    private $server;

    private $redis;
    public function __construct($config)
    {
        $this->server = new \swoole\websocket\server('0.0.0.0',9800);
        $this->server->set($config);
        //注册事件
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('message',[$this,'onMessage']);
        $this->server->on('close',[$this,'onClose']);
        $this->server->start();
    }

    //工作进程启动
    public function onWorkerStart($server,$worker_id)
    {
        //给每个工作进程实例Redis
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1',6379);
    }

    //客户发送消息时触发
    public function onMessage($server,$frame)
    {
//        common::dump($frame);
        //客户端发送的数据
        $data = json_decode($frame->data,true);
        //客户端ID
        $fd = $frame->fd;
        common::dump($data);

    }

    //客户端关闭时触发
    public function onClose($server,$fd)
    {
        common::dump("客户端关闭 {$fd}");
    }
}

$config = [
    'worker_num' => 3,//工作进程量
    'package_max_num' => 1024*1024*10,
    'max_request' => 3000,
    'heartbeat_idle_time' => 10,//允许最大的空闲时间
    'heartbeat_check_interval' => 5,//定时检测在线列表
];

new Rpc($config);

?>


















