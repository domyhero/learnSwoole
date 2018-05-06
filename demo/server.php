<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/5
 * Time: 下午11:31
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

use learnswoole\common\tcpPack;
use learnswoole\common\common;

$serv = new swoole_server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

$serv->set([
    'worker_num' => 4,//设置工作进程
    'reactor_num' => 6,//线程组个数
    'max_request' => 1000, //最大连接
    'daemonize'=>true,    //后台挂起
    'log_file'=>__DIR__.'/server.log', //
    'open_length_check' => true,
    'package_max_length' => 1024 * 1024,//总的请求数据大小 字节为单位
    'package_length_type' => 'N',//php 的 pack 函数一致 N|n 无符号大|小网络字节
    'package_length_offset' => 0,//计算总长度
    'package_body_offset' => 4,//包头位置
    'heartbeat_check_interval' => 3, //心跳检测 3秒一次所有
    'heartbeat_idle_time' => 5,    //连接最大时间为5秒
]);

//主进程
$serv->on('start', function ($serv) {
//    common::dump('主进程启动');
});

//管理进程
$serv->on('managerStart', function ($serv) {
//    common::dump('管理进程启动');
});

//工作进程
$serv->on('WorkerStart', function ($serv, $worker_id) {
    common::dump('工作进程启动 主进程pid:' . $serv->master_pid .'工作进程ID: '.$worker_id);
    //为什么需要每一个worker进程都加载一次? 因为每个进程相互独立,互不影响
    //为每个独立的工作进程 加载自动加载类 方便调用业务逻辑区代码
    require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
});

//监听连接进入事件,有客户端连接进来的时候会触发
$serv->on('connect', function ($serv, $fd, $from_id) {
//    common::dump('客户端' . $fd . '连接');
});

//监听数据接收事件,server接收到客户端的数据后,worker进程内触发该回调
$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    common::dump("收到客户端消息".$data."|");
    //得到包体长度
    $body = tcpPack::de_pack_body($data);
    if (is_string($body)) {
        //如果是字符串请求 则默认请求欢迎信息
        $welcome = new \learnswoole\core\welcome();
        $reply = $welcome->index();
    } else {
        if ($body['type'] == 'connect') {
            $reply = "我收到了你的数组请求";
        } elseif ($body['type'] == 'shop') {
            $shop = new \learnswoole\core\shop();
            $reply = $shop->index();
        }
    }
    $reply = tcpPack::en_pack_body($reply);
    $serv->send($fd,$reply);
});

//监听数据关闭事件,客户端关闭,或者服务端主动关闭
$serv->on('close', function ($serv, $fd) {
    common::dump('客户端 : ' . $fd . '关闭了');
});

//启动服务
$serv->start();

