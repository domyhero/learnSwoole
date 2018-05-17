<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/13
 * Time: 下午12:54
 */

namespace learnswoole\tool;
use learnswoole\common\common;

class httpClient
{

    private $client;

    function __construct($ip,$port)
    {
        $this->client = new \swoole\http\client($ip,$port);
    }

    //异步http请求
    public function asyncHttp()
    {
        
    }
    
    //异步websocket请求
    public function asyncWebsocket($callback,$callbackSend)
    {
        //接收到服务端发送的消息
        $this->client->on('message', $callbackSend);

        //发起websocket 握手请求并将连接升级为websocket 请求
        ### 这里使用callback 回调参数 是因为 将闭包放置外部使用,对程序做解耦,用于应对不同的场景和需求
        $this->client->upgrade('/',$callback);
    }

}