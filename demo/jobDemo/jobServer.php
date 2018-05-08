<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/7
 * Time: 下午11:25
 */

require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';

use learnswoole\common\common;
use learnswoole\common\tcpPack;

class jobServer
{
    private $serv;

    private $config;

    public function __construct($config= [],$connectConfig=[])
    {
        (!empty($config) && !empty($connectConfig)) || exit("invalid configs , ERROR: ".__FILE__." line: ".__LINE__);
        //全局对象
        $this->serv = new \swoole\server($connectConfig['servHost'],$connectConfig['servPort'],$connectConfig['servMode'],$connectConfig['servSockType']);

        //配置
        $this->serv->set($config);


        //注册事件
        $this->serv->on('start',[$this,'onStart']);
        $this->serv->on('managerStart',[$this,'onManagerStart']);
        $this->serv->on('workerStart',[$this,'onWorkerStart']);
        $this->serv->on('connect',[$this,'onConnect']);
        $this->serv->on('receive',[$this,'onReceive']);
        $this->serv->on('close',[$this,'onClose']);
        $this->serv->on('task',[$this,'onTask']);
        $this->serv->on('finish',[$this,'onFinish']);

        //启动服务
        $this->serv->start();
    }

    //主进程启动
    public function onStart($serv)
    {

    }

    //管理进程启动
    public function onManagerStart($serv)
    {

    }

    //工作进程启动
    public function onWorkerStart($serv,$worker_id)
    {

    }

    //连接客户端触发
    public function onConnect($serv,$fd,$reactor_id)
    {
        //有客户端连接时 投递任务到task进程当中
//        for ($i = 0 ; $i < 18; $i++) {
        $i = 11;
            $serv->task($i);
            common::dump('投递 任务 :'.$i);
            sleep(5);//阻塞3秒
//        }

    }

    //接收客户端消息触发
    public function onReceive($serv,$fd,$reactor_id,$data)
    {

    }

    //客户端连接关闭触发¡
    public function onClose($serv,$fd,$reactor_id)
    {

    }

    //接收到工作进程投递任务task_worker事件触发
    public function onTask($serv,$task_id,$src_worker_id,$data)
    {
        common::dump('当前排队的task任务数量 : '.$serv->stats()['tasking_num']);
        common::dump('消息任务编号为: '.$task_id.',来自worker: '.$src_worker_id.' 的投递');
        sleep(10);//阻塞10秒;
        return '完毕了';
    }

    //task_worker 任务执行完毕触发
    public function onFinish($serv,$task_id,$data)
    {
        common::dump('消息任务编号为: '.$task_id.' '.$data);

    }
}

new jobServer(common::server_tcp_configs(),common::connect_config());