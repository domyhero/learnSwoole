<?php
/**
 * Created by PhpStorm.
 * User: suntoo-ssd-02
 * Date: 2018/5/17
 * Time: 14:27
 */

namespace learnswoole\tool;


class TcpClient
{
    private $client;
    private $ip;
    private $port;

    public function __construct($ip,$port)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->client = new \swoole\client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
    }

    //同步请求
    public function sync($data)
    {
        if ($this->client->connect($this->ip, $this->port)) {
            $this->client->send($data);//发送数据
            $data = $this->client->recv();//接收
            $this->client->close();
            return $data;
        }
    }

}