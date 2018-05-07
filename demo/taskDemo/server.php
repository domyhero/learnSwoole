<?php

//----------此方法里面使用的全局变量,只针对各个进程,并不能做到各个进程之间内存共享---------
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/6
 * Time: 下午6:35
 */
require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\tcpPack;
use learnswoole\common\common;
//定义一个全局task工作进程状态数组
$task_worker_ids = [];
$test = '苹果';
//实例化服务端
$serv = new swoole_server('0.0.0.0',9501,SWOOLE_PROCESS,SWOOLE_SOCK_TCP);
//配置
$serv->set([
    'worker_num' => 2,      //worker进程数
    'reactor_num' => 4,     //线程组数
    'task_worker_num' => 5, //taskWorker进程数
    //设置tcp包体协议
    'open_length_check' => true, //开启长度检测
    'package_max_length' => 1024 * 1024,//总的请求数据大小 字节为单位
    'package_length_type' => 'N',//php 的 pack 函数一致 N|n 无符号大|小网络字节
    'package_length_offset' => 0,//计算总长度
    'package_body_offset' => 4,//包头位置

]);
//主进程启动
$serv->on('start', function ($serv) {
    common::dump('主进程启动');
    common::dump($GLOBALS['test']);
    $GLOBALS['test'] = '香蕉';

});
//管理进程启动
$serv->on('managerStart', function ($serv) {
        common::dump('管理进程启动');
        common::dump($GLOBALS['test']);
});
//工作进程启动
$serv->on('workerStart', function ($serv, $worker_id) {
    common::dump('工作进程启动');
    common::dump($GLOBALS['test']);
    //因为每个进程之间是独立存在的,所以给每个工作进程加载文件,这里有worker进程和taskworker进程
    require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
    //设置全局task变量的初始状态
//    if ($worker_id == 0) {
        $task_worker_num = $serv->setting['task_worker_num'];
        $tempArr = [];
        for ($i = 0 ;$i < $task_worker_num; $i++) {
            $tempArr[]  = $i;
        }
        $GLOBALS['task_worker_ids'] = $tempArr;
//    }

});
//监听客户端连接触发
$serv->on('connect', function ($serv, $fd) {
    common::dump('客户端连接');
    common::dump($GLOBALS['test']);
    $GLOBALS['test'] = '客户端改苹果';
    common::dump($GLOBALS['test']);
    common::dump("有新的连接 标识 : {$fd} ");
});
//监听接收客户端消息触发
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    common::dump('接收到客户端消息');
    common::dump($GLOBALS['test']);
    //处理客户端消息
    $body = tcpPack::de_pack_body($data);
    if (!empty($body) && $body['type'] == 'task') {
        //获取请求数据总量
        $countdata = count($body['data']);
        //获取当前没有工作的task进程数量
        $taskWorkerNum = count($GLOBALS['task_worker_ids']);
        //组装分批数据
        $divisionData = array_chunk($body['data'],ceil($countdata/$taskWorkerNum));
        //分配给task进程处理
        $task_ids = $GLOBALS['task_worker_ids'];
        foreach ($divisionData as $k => $v) {
            //获取task闲置进程ID并让其工作
            if ($taskWorkerNum > 0) {
                //利用循环 取出并让其重置
                foreach ($task_ids as $gk => $src_task_id) {
                    if ($src_task_id >= 0) {
                        $val['src_task_id'] = $src_task_id;
                        unset($task_ids[$gk]);
                        $serv->task($val,$src_task_id);
                        break;
                    }
                }
            }
        }
        $GLOBALS['task_worker_ids'] = $task_ids;
    }
});
//监听客户端关闭触发
$serv->on('close', function ($serv, $fd, $reactor_id) {

});
//监听有worker进程,调用的taskWorker时触发
$serv->on('task', function ($serv, $task_id, $src_worker_id, $data) {
    common::dump('taskWorke');
    common::dump($GLOBALS['test']);
    common::dump("task工作开始进程 任务ID : {$task_id}, 来自工作进程 {$src_worker_id}");
    $outData = ['success'=>['src_task_id'=>$data['src_task_id']]];
    sleep(5);
    return $outData;
});
//监听taskWorker进程完成时触发,此处的task_id 是任务ID
$serv->on('finish', function ($serv, $task_id, $data) {
    $task_ids = $GLOBALS['task_worker_ids'];
    if (isset($data['success'])) {
        $src_task_id = $data['success']['src_task_id'];
        $task_ids[] = $src_task_id;
        $GLOBALS['task_worker_ids'] = $task_ids;
        common::dump("taskWorker进程ID: {$src_task_id} 信息处理完毕 , task 任务ID : {$task_id}");
    }
});
//启动服务
$serv->start();
