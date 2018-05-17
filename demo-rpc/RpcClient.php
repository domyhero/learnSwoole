<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
use learnswoole\tool\TcpClient;
class RpcClient
{
    private $serverName;

    //调用不存在的方法时触发
    function __call($name, $arguments)
    {
        if ($name == 'service') {
            //意味着会调用
            $this->serverName = $arguments[0];
            return $this;
        }

        //发送tcp 请求去 服务中心
        $client = new TcpClient('127.0.0.1',9500);

        //请求服务名称,参数
        $data = json_encode([
            'service' => $this->serverName,//服务名
            'action' => $name,//方法
            'params' => $arguments,//参数
        ]);


        $res = $client->sync($data);
        return $res;

    }

}