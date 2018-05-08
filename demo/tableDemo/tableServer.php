<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/8
 * Time: 下午9:14
 */

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'autoloader.php';

use learnswoole\common\common;
use learnswoole\common\tcpPack;

class tableServer
{
    //服务端对象
    private $serv;
    //共享内存对象
    private $table;
    //task_worker进程数量
    private $task_worker_num;

    public function __construct($config = [], $connectConfig = [])
    {
        (!empty($config) && !empty($connectConfig)) || exit("invalid configs , ERROR: ".__FILE__." line: ".__LINE__);

        //全局对象
        $this->serv = new \swoole\server($connectConfig['servHost'],$connectConfig['servPort'],$connectConfig['servMode'],$connectConfig['servSockType']);

        //配置
        $this->serv->set($config);

        //启动内存共享
        $this->table = new \swoole\table(1024);

        //设置字段 和类型 和大小
        $this->table->column('task_worker_id',\swoole\table::TYPE_INT,1);
        $this->table->column('status',\swoole\table::TYPE_INT,1);

        //进行创建
        $this->table->create();

        //task_worker_num进程数量
        $this->task_worker_num = $this->serv->setting['task_worker_num'];

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
        //根据当前的task_worker_num 个数,决定创建多少个记录, 0 : 空闲  1 : 忙碌
        for ($i = 0 ; $i < $this->task_worker_num; $i++) {
            $this->table->set($i,['task_worker_id'=>$i,'status'=>0]);
        }

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
//        common::dump('有新的连接 : '.$fd);

//        $serv->send($fd,tcpPack::en_pack_body('你好'));
    }

    //接收客户端消息触发
    public function onReceive($serv,$fd,$reactor_id,$data)
    {
        $body = tcpPack::de_pack_body($data);
        common::dump($body);
        //模拟客服端发送的数据
        $data = [];
        for ($i = 0; $i < 18;$i++) {
            $data[$i] = ['id'=>$i,'name'=>'很大的数据'];
        }

        //查询task进程状态
        $task = [];
        foreach ($this->table as $row) {
            //状态为0,证明当前是空闲的task
            if ($row['status'] == 0) {
                $task[]['task_worker_id'] = $row['task_worker_id'];
            }
        }
        //得到闲置task进程的数量
        $taskCount = count($task);
        if ($taskCount > 0 && $taskCount != $this->task_worker_num) {
            //如果有闲置task进程且不是所有进程都闲置 则进行分割发配任务
            $data = array_chunk($data,ceil(count($data)/$taskCount));
            foreach ($data as $k => $val) {
                $val['task_worker_id'] = $task[$k]['task_worker_id'];
                $serv->task($val,$task[$k]['task_worker_id']);
                sleep(mt_rand(1,10));
                common::dump("我是空闲状态的投递===============================================> 空闲投递");
            }
        } else {
            //否则平均分配任务
            $data = array_chunk($data,ceil(count($data)/$this->task_worker_num));
            foreach ($data as $task_worker_id => $val) {
                $val['task_worker_id'] = $task_worker_id;
                $serv->task($val,$task_worker_id);
                sleep(mt_rand(1,10));//随机1-10秒投递,用于模拟,便于测试结果
                common::dump("-------平均投递------");
            }

        }


    }

    //客户端连接关闭触发¡
    public function onClose($serv,$fd,$reactor_id)
    {
        common::dump('客户端 : '.$fd.' 关闭了');
    }

    //接收到工作进程投递任务task_worker事件触发
    public function onTask($serv,$task_id,$src_worker_id,$data)
    {
        common::dump('当前排队的task任务数量 : '.$serv->stats()['tasking_num']);
        common::dump('消息任务编号为: '.$task_id.',来自worker: '.$src_worker_id.' 的投递');
        //更新某个task进程状态,忙碌
        $this->table->incr($data['task_worker_id'],'status',1);//增加1
        sleep(mt_rand(1,20));//阻塞20秒;
        return ['task_worker_id' => $data['task_worker_id']];
    }

    //task_worker 任务执行完毕触发
    public function onFinish($serv,$task_id,$data)
    {
//        common::dump('消息任务编号为: '.$task_id.' '.$data);
        //将某个task进程状态给为空闲状态
        $this->table->decr($data['task_worker_id'],'status',1);
    }
}

new tableServer(common::server_tcp_configs(),common::connect_config());