<?php

/**
 * 购物车代码服务端
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 9:12
 */
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\httpClient;
class CartServer
{
    private $serv;
    public $data;
    private $tcpServ;

    function __construct($config,$cliData)
    {
        $this->serv = new \swoole\websocket\server('0.0.0.0',9502);
        $this->data = $cliData;

        $this->serv->set($config);

        $this->serv->on('workerStart', [$this, 'onWorkerStart']);
        $this->serv->on('message', [$this, 'onMessage']);
        $this->serv->on('close', [$this, 'onClose']);
        $this->serv->on('receive',[$this,'onReceive']);
        $this->serv->on('request',[$this,'onRequest']);

        //监听tcp链接


        $this->serv->start();
    }

    public function onWorkerStart($serv,$worker_id)
    {
        common::dump("CartServer : 工作进程启动 : {$worker_id}");
        //向服务中心注册当前服务,告知当前服务的可用性  这里只需要注册一次即可
        if ($worker_id == 0) {
            $data = $this->data;
            $data['method'] = 'register';
            //实例化注册中心客户端 即与注册中心进行连接
            $webCli = new httpClient('127.0.0.1',9501);
            $webCli->asyncWebsocket(function ($cli) use ($data) {
                //发送注册信息
                $cli->push(json_encode($data));

                //定时发送心跳包,防止注册中心关闭连接
                swoole_timer_tick(2000, function ($id) use ($cli) {
                   $cli->push('',9);//发送ping 包, 此类型ping包为swoole底层设计,即不会触发onmessage事件
                });
            }, function ($cli,$frame) {
                if (!empty($frame->data)) {
                    common::dump("接收到消息 : ");
                    common::dump($frame->data);
                }

            });
        }
    }

    public function onMessage($serv,$frame)
    {
        common::dump("CartServer : 接收到消息 : ");
        common::dump($frame->data);

        //将处理后的数据返出
        $serv->send($frame->fd,'ok,i know you');
    }

    public function onClose($serv,$fd,$reactorId)
    {
        common::dump("客户端 : {$fd} 关闭了");
    }

    public function onReceive()
    {
        
    }

    public function onRequest()
    {
        
    }

}

$config = [
    'worker_num' => 1,
    'package_max_length' => 1024*1024*10,
];

$cliData = [
    'ip' => '127.0.0.1',
    'port' => 9502,
    'serverName' => 'CartServer'
];

new CartServer($config,$cliData);