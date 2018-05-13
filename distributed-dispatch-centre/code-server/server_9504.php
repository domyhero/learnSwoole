<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/13
 * Time: 下午1:18
 */

class server_9503
{
    public $server;
    
    public function __construct($config)
    {
        //全局对象
        $this->server = new \swoole\http\server('0.0.0.0',9503);
        $this->server->set($config);
        
        //注册事件
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('message',[$this,'onMessage']);
        
        
        $this->server->start();
    }


    public function onWorkerStart()
    {
        
    }

    public function onMessage()
    {
        
    }

}