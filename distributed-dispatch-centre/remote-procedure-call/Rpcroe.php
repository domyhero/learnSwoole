<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/16
 * Time: 下午9:22
 */
require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
class Rpcroe
{
    public $serv;
    public $redis;

    public function __construct($config)
    {
        $this->serv = new \swoole\websocket\server('0.0.0.0',9501);

        $this->serv->set($config);

        $this->serv->on('workerStart',[$this,'onWorkerStart']);
        $this->serv->on('message',[$this,'onMessage']);
        $this->serv->on('close',[$this,'onClose']);
        $this->serv->on('open',[$this,'onOpen']);

        $this->serv->start();
    }


    //工作进程启动
    public function onWorkerStart($serv,$worker_id)
    {
        //给每个工作进程实例化一个Redis连接
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1',6379);
    }

    //客户端连接完成触发
    public function onOpen($serv,$req)
    {
        common::dump($req);
    }

    //接收到客户端消息触发
    public function onMessage($serv,$frame)
    {
        //接收到消息后 判断是否是注册
        $data = json_decode($frame->data, true);
        $fd = $frame->fd;
        if (isset($data['method']) && $data['method'] == 'register') {
            $server_key = 'server:'.$data['serverName'];
            $value = json_encode([
                'ip' => $data['ip'],
                'port' => $data['port'],
            ]);

            //进行Redis 存储
            $res = $this->redis->sAdd($server_key,$value);
            common::dump($server_key);
            common::dump($value);
            $redis = $this->redis;
            //利用定时器,检测代码服务端的存货状态
            if ($res) {
                $serv->tick(3000, function ($id) use ($serv,$server_key,$value,$redis,$fd) {
                    //如果不是存活状态 则清除 Redis中的这个服务
                    if (!$serv->exist($fd)) {
                        //检测服务在不在redis中,在则移除
                        if ($redis->SISMEMBER($server_key,$value)) {
                            $redis->sRem($server_key,$value);
                        }
                        //获取集合当中的成员
                        common::dump($redis->sMembers($server_key));

                        //清除定时器
                        $serv->clearTimer($id);
                    }
                });
            }
        } else {
            $serv->push($frame->fd,'非法请求');
        }
        
    }

    public function onClose($serv,$fd,$reactorId)
    {
        common::dump('客户端关闭了 : '.$fd);
    }



}

$config = [
    'worker_num' => 2,//开启5个工作进程
    'max_request' => 3000,//最大连接数为3000
    'package_max_num' => 1024*1024*10,//传输最大10兆
    'heartbeat_idle_time' => 10,//允许最大的空闲时间
    'heartbeat_check_interval' => 5,//定时检测在线列表

];

new Rpcroe($config);

