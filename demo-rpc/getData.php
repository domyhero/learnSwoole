<?php
/**
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 17:14
 */
include_once __DIR__.DIRECTORY_SEPARATOR.'RpcClient.php';

$client = new RpcClient();
$client->service('CartServer');

$data = $client->getData(['name'=>'dream','sign'=>'520']);

var_dump($data);

