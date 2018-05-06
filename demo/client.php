<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/5
 * Time: 下午10:36
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

use learnswoole\common\tcpPack;
use learnswoole\common\common;

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$client->set([
    'open_length_check' => true, //开启长度检测
    'package_max_length' => 1024 * 1024,//总的请求数据大小 字节为单位
    'package_length_type' => 'N',//php 的 pack 函数一致 N|n 无符号大|小网络字节
    'package_length_offset' => 0,//计算总长度
    'package_body_offset' => 4,//包头位置
]);

//连接服务端
$client->on('connect', function (swoole_client $cli) {
    //客户端连接
    $data= array(
        'type' => 'connect',
//        'type' => 'shop',
        'data' => 111
    );
//    $data = '111';
    //包头(length:是包体长度)+包体
    $packge = tcpPack::en_pack_body($data);
    //连续发送5条数据
//    for ($i=0;$i<15;$i++){
        $cli->send($packge);
//    }
});

//接收到服务端发送的消息时触发
$client->on('receive', function ($cli, $data) {

    $body = tcpPack::de_pack_body($data);
    common::dump("收到服务端消息:");
    common::dump($body);
});

//连接错误提醒
$client->on('error', function ($cli) {

});

//监听连接关闭事件,客户端关闭,或者服务器主动关闭
$client->on('close', function ($cli) {

});

//先绑定事件之后随后建立连接,连接失败直接退出并打印错误码
$client->connect('127.0.0.1', 9501) || exit("连接失败 . Error : {$client->errCode}\n");

