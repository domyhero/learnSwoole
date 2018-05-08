<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/8
 * Time: 上午12:48
 */


require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';

use learnswoole\common\tcpPack;
use learnswoole\common\common;

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$client->set(common::client_tcp_configs());

//连接上服务端触发
$client->on('connect', function (swoole_client $cli) {

});

$client->on('receive', function ($cli, $data) {

});

$client->on('error', function ($cli) {

});

$client->on('close', function ($cli) {

});

$client->connect(common::connect_config('cliHost'),common::connect_config('cliPort'));