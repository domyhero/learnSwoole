<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/8
 * Time: 下午9:36
 */

require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR."autoloader.php";

use learnswoole\common\common;
use learnswoole\common\tcpPack;

//实例化swoole
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

//设置
$client->set(common::client_tcp_configs());

//连接服务端触发
$client->on('connect', function (swoole_client $cli) {
//    common::dump('连接成功!~');
    //给服务端发送消息
    $packge = tcpPack::en_pack_body('一个很大的数据包');

    $cli->send($packge);

});

//接收服务端消息触发
$client->on('receive', function ($cli, $data) {
    $body = tcpPack::de_pack_body($data);
    common::dump($body);
});

//错误触发
$client->on('error', function ($cli) {
    common::dump($cli->errCode);
});

//客户端关闭,服务端主动关闭触发
$client->on('close', function ($cli) {
    common::dump('关闭了');
});

//绑定事件后建立连接,失败返回错误码
$client->connect(common::connect_config('cliHost'),common::connect_config('cliPort')) || exit("连接失败: {$client->errCode}\n");