<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/6
 * Time: 下午6:27
 */
require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';

use learnswoole\common\tcpPack;
use learnswoole\common\common;

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$client->set([
    //设置tcp包体协议
    'open_length_check' => true, //开启长度检测
    'package_max_length' => 1024 * 1024,//总的请求数据大小 字节为单位
    'package_length_type' => 'N',//php 的 pack 函数一致 N|n 无符号大|小网络字节
    'package_length_offset' => 0,//计算总长度
    'package_body_offset' => 4,//包头位置
]);

//连接上服务端触发
$client->on('connect', function (swoole_client $cli) {
    //此处因模拟100W数据请求 测试分批task处理
    $taskdata = [];
    for ($i = 0; $i < 17; $i++) {
        $taskdata[] = ['pic_id'=>$i,'data'=>"请求task数据{$i}"];
    }
    $data = array(
        'type' => 'task',
        'data' => $taskdata
    );
    $packge = tcpPack::en_pack_body($data);
    $cli->send($packge);
});

$client->on('receive', function ($cli, $data) {

});

$client->on('error', function ($cli) {

});

$client->on('close', function ($cli) {

});

$client->connect('127.0.0.1',9501,6);