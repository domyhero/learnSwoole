<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/20
 * Time: 下午12:34
 */

namespace home\tool;


use home\common\Common;

class HttpClient extends Common
{
    public $client;

    public function __construct($ip,$port)
    {
        parent::__construct();
        $this->client = new \swoole\http\client($ip,$port);
    }

    //同步请求客户端
    public function http()
    {

    }

    //异步websocket请求
    public function async_websocket($callback,$path='/')
    {
        //监听服务端给我们发送的数据
        $this->client->on('message', function ($cli, $frame) {

        });

        //websocket 建立的是一个长连接  升级为websocket 连接
        $this->client->upgrade($path, $callback);
    }

}