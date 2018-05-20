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
    public $client;
    
    
    public function __construct($ip,$port)
    {

        $this->client = new \swoole\http\client($ip,$port);

    }
    
    //异步请求http客户端
    public function http()
    {
        
    }

    //异步请求websocket客户端
    public function async_websocket($callback,$path='/')
    {

        //监听服务端给我们发送的消息
        $this->client->on('message', function ($cli, $frame) {
//           common::dump('接受到消息');
        });

        //websocket建立一个长连接
        $this->client->upgrade($path, $callback);
        
    }
    
    //同步请求客户端

}