<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/20
 * Time: 下午12:10
 */

namespace home\distributed;


use home\common\Common;
use home\tool\HttpClient;

class Server_9503 extends Common
{
    public $serv;

    public function __construct()
    {
        parent::__construct();
        $this->serv = new \swoole\websocket\server('0.0.0.0',$this->config['distributed']['server_9503']['websocket_port']);
        
        $this->serv->set($this->config['distributed']['server_9503']['websocket_set']);
        
        //注册事件
        $this->serv->on('WorkerStart',[$this,'onWorkerStart']);
        $this->serv->on('message',[$this,'onMessage']);
        $this->serv->start(); //全局生命周期
    }

    //工作进程启动触发
    public function onWorkerStart($serv,$worker_id)
    {
        //向注册中心发送注册信息 只注册一次
        if ($worker_id == 0) {
            //组装当前服务的信息
            $data['ip'] = $this->config['distributed']['server_9503']['ip'];
            $data['serviceName'] = $this->config['distributed']['server_9503']['serviceName'];
            $data['port'] = array(
                'ws' => $this->config['distributed']['server_9503']['websocket_port'],
            );
            $data['method'] = 'register';   //标识 代表注册服务方法

            //发送请求 注册中心
            $websocket = new HttpClient($this->config['rpc']['ip'],$this->config['rpc']['websocket_port']);

            //异步websocket请求
            $websocket->async_websocket(function ($cli) use ($data,$serv) {
                //检测当前连接没有问题 则发送注册信息
                if ($cli->errCode == 0) {
                    //连接没问题
                    $cli->push(json_encode($data));//发送注册消息
                    Common::dump("当前websocket连接没有问题,发送注册信息 : ");
                    Common::dump($data);
                } else {
                    //关闭服务
                    Common::dump("当前websocket连接有问题,关闭服务");
                    $serv->shutdown();
                }

                //定时发送心跳包,维持存货状态
                swoole_timer_tick(2000, function ($id) use ($cli) {
                    $cli->push('',9); //ping包,不会触发onMessage事件
                });

                //向注册中心 报告当前的机器状态
                swoole_timer_tick(2000, function ($id) use ($cli,$serv) {
                    $data = $serv->stats();
                    //当前系统的负载
                    $load = sys_getloadavg();
                    //组装服务器状态
                    $status = [
                        'method' => 'status',//标识  报告状态方法
                        'ip' => $this->config['distributed']['server_9503']['ip'],//当前服务的IP
                        'port' => array(
                            'ws' => $this->config['distributed']['server_9503']['websocket_port'],//websocket 端口
                        ),
                        'load' => $load[2],
                        'connection_num' => $data['connection_num'], //连接数量
                        'tasking_num' => $data['tasking_num'], //task任务排队数量
                    ];

                    //给注册中心发送状态
                    $cli->push(json_encode($status));
                });
            });

        }
    }

    //接收客户端消息是触发
    public function onMessage($serv,$frame)
    {

    }

}




