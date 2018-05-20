<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/13
 * Time: 下午1:18
 */

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'autoloader.php';

use learnswoole\tool\httpClient;

class server_9504
{
    public $server;
    private $data; //用于rpc服务中心注册信息
    
    public function __construct($config,$data)
    {
        //全局对象
        $this->server = new \swoole\websocket\server('0.0.0.0',9504);
        $this->server->set($config);

        $this->data = $data;
        
        //注册事件
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('message',[$this,'onMessage']);
        //全局生命周期
        $this->server->start();
    }


    //工作进程启动
    public function onWorkerStart($server,$worker_id)
    {
        //在工作进程启动时 去服务中心 注册,且只注册一次
        if ($worker_id == 0) {
            $data = $this->data;
            $data['method'] = 'register'; //代表当前是注册服务的方法

            //发送请求注册中心
            $websocket = new httpClient('127.0.0.1',9800);
            //异步websocket请求
            $websocket->async_websocket(function ($cli) use ($data) {
                //发送一个消息去注册中心
                $cli->push(json_encode($data));
            });
        }
        
    }

    //接收到消息时触发
    public function onMessage()
    {
        
    }

}
$config = [
    'worker_num' => 2,//工作进程量
    'package_max_length' => 1024*1024*10,//最大请求长度
    'max_request' => 3000,//最大请求次数,达到后自动重启,用于防止内存溢出
];
$data = [
    'ip' => '172.0.0.1',//记录当前的IP,便于用户中心注册
    'prot' => 9504,
    'serviceName' => 'ShopService'
];

new server_9504($config,$data);

