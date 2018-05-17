<?php

/**
 * 服务中心
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 9:12
 */
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\TcpClient;
use learnswoole\tool\httpClient;
class Rpc
{
    private $serv;
    private $redis;
    protected $tcpServer;

    function __construct($config)
    {
        $this->serv = new \swoole\websocket\server('0.0.0.0',9501);
        //配置
        $this->serv->set($config);
        //注册事件
        $this->serv->on('workerStart', [$this, 'onWorkerStart']);
        $this->serv->on('message', [$this, 'onMessage']);
        $this->serv->on('close', [$this, 'onClose']);
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('request', [$this, 'onRequest']);


        //监听tcp 连接
        $this->tcpServer = $this->serv->addlistener('0.0.0.0',9500,SWOOLE_SOCK_TCP);
        $this->tcpServer->set([
            'worker_num' => 2,
            'package_max_length' => 1024*1024*10,
            'max_request' => 3000,
        ]);


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

    //接收到tcp连接消息时触发
    public function onReceive($serv,$fd,$reactot_id,$data)
    {
        common::dump($data);
        //接收消息 做服务分发
        $this->rpcSend($data,$serv,$fd);
//        common::dump("rpc服务中心 将得到的数据 返回给 客户端");
//        将数据返回给客户端
//        $serv->send($fd,$res);

    }

    //rpc服务分发
    public function rpcSend($data,$serv,$fd)
    {
        $data = json_decode($data,true);
        //获取服务名称
        $serverName = 'server:'.$data['service'];
        //组装请求数据
        $request['action'] = $data['action'];
        $request['params'] = $data['params'];
        $request['method'] = 'getData';//获取数据方法

        //判断当前的服务是否存活,健康检查,否则返回404
        $codeServer = $this->redis->SMEMBERS($serverName);
        common::dump("rpc服务分发中检测 {$serverName} 的存活状态:");
        common::dump($codeServer);
        if (empty($codeServer)) {
            //直接返回404 页面
            return '404 not found';
        }
        //中间环节 可以根据多个同样的服务不同的ip 进行一个择优 负载均衡设置
        //....

        //这里直接请求处理
        $codeServerData = json_decode($codeServer[0],true);

        common::dump($codeServerData);
        //调用代码服务端获取数据,那么代码服务端 也需要同步 http 监听 并返回数据
        $client = new httpClient($codeServerData['ip'],$codeServerData['port']);
        common::dump("rpc服务分发中 向 {$serverName} 发送数据请求");
        common::dump($request);
        $client->asyncWebsocket(function ($cli) use ($request) {
            $cli->push(json_encode($request));
        },function ($cli,$frame) use ($serv,$fd) {
            common::dump("服务中心 146 行接收到了信息 ; 接收到消息 : ");
            common::dump($frame->data);
            if (!empty($frame->data)) {
                $serv->send($fd,$frame->data);
            }
        });
    }

    public function onRequest()
    {
        
    }

    public function onClose($serv,$fd,$reactorId)
    {
        common::dump("标识为 : {$fd}的客户端关闭了");
    }

}

$config = [
    'worker_num' => 1,//2个工作进程
    'package_max_length' => 1024*1024*10,
    'max_request' => 3000,
    'heartbeat_idle_time' => 6,
    'heartbeat_check_interval' => 2,
];

new Rpc($config);