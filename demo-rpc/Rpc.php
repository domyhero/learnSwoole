<?php

/**
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 9:12
 */
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
class Rpc
{


    private $serv;
    private $redis;

    function __construct($config)
    {
        $this->serv = new \swoole\websocket\server('0.0.0.0',9501);
        //配置
        $this->serv->set($config);
        //注册事件
        $this->serv->on('workerStart', [$this, 'onWorkerStart']);
        $this->serv->on('message', [$this, 'onMessage']);
        $this->serv->on('close', [$this, 'onClose']);
        $this->serv->start();
    }


    public function onWorkerStart($serv,$worker_id)
    {
        common::dump("工作进程 : {$worker_id} 启动,并给每个进程赋值了redis连接.");
        //每个工作进程启动时 给一个redis链接
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1',6379);
    }

    public function onMessage($serv,$frame)
    {
        //接收客户端的信息
        $data = json_decode($frame->data,true);
        //接收客户端标识
        $fd = $frame->fd;
        //判断是否是注册信息
        if (isset($data['method']) && $data['method'] == 'register') {
            //组装redis存储数据
            $server_key = "server:{$data['serverName']}";
            $value['ip'] = $data['ip'];
            $value['port'] = $data['port'];
            $value = json_encode($value);
            //查询redis中是否有该服务
            $redis = $this->redis;
            $server_redis_status = $redis->SISMEMBER($server_key,$value);
            common::dump("redis 查 {$server_key} 的存活状态 : {$server_redis_status}");
            $redis_key_values = $redis->sMembers($server_key);
            common::dump("查了 key {$server_key} 的值 : ");
            common::dump($redis_key_values);
            if (!$server_redis_status) {
                //如果不存在则进行添加
                $res = $redis->sAdd($server_key,$value);
                if ($res) {
                    common::dump("添加了redis key : {$server_key}");
                    $redis_key_values = $redis->sMembers($server_key);
                    common::dump("查了 key {$server_key} 的值 : ");
                    common::dump($redis_key_values);
                    //存好后,需要开启一个定时器 主动监测当前服务的存活状态
                    $serv->tick(200, function ($id) use ($serv,$fd,$server_key,$value,$redis) {
                        //如果主动监测不存在则 清除此服务并清除定时器
                        if (!$serv->exist($fd)) {
                            $redis->sRem($server_key,$value);
                            $serv->clearTimer($id);
                            common::dump("清除了key : {$server_key} ,定时器 : {$id}");
                            $redis_key_values = $redis->sMembers($server_key);
                            common::dump("查了 key {$server_key} 的值 : ");
                            common::dump($redis_key_values);
                        }
                    });
                }
            }
        }


    }

    public function onClose($serv,$fd,$reactorId)
    {
        common::dump("标识为 : {$fd}的客户端关闭了");
    }

}

$config = [
    'worker_num' => 2,//2个工作进程
    'package_max_length' => 1024*1024*10,
    'max_request' => 3000,
    'heartbeat_idle_time' => 6,
    'heartbeat_check_interval' => 2,
];

new Rpc($config);