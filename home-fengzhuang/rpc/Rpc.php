<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/19
 * Time: 下午5:09
 */

namespace home\rpc;

use home\common\Common;

class Rpc extends Common
{
    public $serv;
    public $redis;
    protected $tcpServ;

    public function __construct()
    {
        parent::__construct();
        //全局对象
        $this->serv = new \swoole\websocket\server('0.0.0.0',$this->config['rpc']['websocket_port']);
        $this->serv->set($this->config['rpc']['websocket_set']);
        //注册事件
        $this->serv->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->serv->on('message',[$this,'onMessage']);
        $this->serv->on('receive',[$this,'onReceive']);
        $this->serv->on('request',[$this,'onRequest']);
        $this->serv->on('close',[$this,'onClose']);

        //监听tcp连接
        $this->tcpServ = $this->serv->addlistener('0.0.0.0',$this->config['rpc']['tcp_port'],SWOOLE_SOCK_TCP);
        $this->tcpServ->set($this->config['rpc']['tcp_set']);

        $this->serv->start();
    }

    //工作进程启动
    public  function  onWorkerStart($serv,$worker_id){
        //给每个工作进程 实例化一个Redis连接
        $this->redis=new \Redis();
        $this->redis->connect($this->config['redis']['ip'],$this->config['redis']['port']);
    }

    //接收websocket客户端消息时触发
    public function onMessage($serv,$frame)
    {
        //获取客户端的消息
        $data = json_decode($frame->data,true);
        $fd = $frame->fd;
        //组装 服务状态key
        $status_key = $data['ip'].'-'.json_encode($data['port']);
        //定义Redis连接变量,方便闭包传递
        $redis = $this->redis;
        //根据客户端请求的方法执行相应事件
        if (isset($data['method']) && $data['method'] == 'register') {
            //注册服务事件
            //组装服务名称的key
            $service_key = 'Server:'.$data['serviceName'];
            $value = json_encode(array(
                'ip' => $data['ip'],
                'port' => $data['port']
            ));
            //将服务注册到Redis 中
            $res = $redis->sAdd($service_key,$value);

            Common::dump($service_key.' : redis 服务名称注册成功');

            //利用定时器,检测代码服务端的存货状态
            if ($res) {
                $serv->tick(3000, function ($id) use ($serv,$service_key,$value,$redis,$fd,$status_key) {
                    //不是存活状态,则移除Redis中的服务
                    if (!$serv->exist($fd)) {
                        //检测是否存在Redis中
                        if ($redis->SISMEMBER($service_key,$value)) {
                            //移除注册信息
                            $redis->sRem($service_key,$value);
                            //删除 此服务的状态信息
                            $redis->del($status_key);
                        }
                        //清除定时器
                        $serv->clearTimer($id);
                    }
                });
            }
        } elseif (isset($data['method']) && $data['method'] == 'status') {
            //存储服务的状态信息
            //字符串
            //IP + 端口  => 系统状态
            $data = array(
                'load' => $data['load'],
                'connection_num' => $data['connection_num'],
                'tasking_num' => $data['tasking_num']
            );
            $redis->set($status_key,json_encode($data));
            Common::dump($status_key." : Redis 服务状态设置成功");
            Common::dump($redis->get($status_key));
        } else {
            $serv->push($fd,'非法请求');
        }

    }

    //接收tcp客户端消息时触发
    public function onReceive($serv,$fd,$reactor_id,$data)
    {
        //接收客户端的信息
        $data = Common::de_pack_body($data);
        Common::dump($data);

    }

    //接收http客户端消息时触发
    public function onRequest($request,$response)
    {

    }

    //客户端关闭时触发
    public function onClose($serv,$fd)
    {
        Common::dump("客户端 {$fd} 关闭了");
    }

    //发送一个http服务调用请求
    public function send()
    {

    }
}

new Rpc();