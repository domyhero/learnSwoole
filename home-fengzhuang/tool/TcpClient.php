<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/20
 * Time: 上午10:40
 */

namespace home\tool;

use home\common\Common;

class TcpClient extends Common
{
    private $client;
    private $ip;
    private $port;
    public function __construct($ip,$port)
    {
        parent::__construct();
        $this->ip = $ip;
        $this->port = $port;
        $this->client = new \swoole\client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
        $this->client->set($this->config['body_pack_config']);
    }

    //同步请求客户端
    public function sync($data)
    {
        if ($this->client->connect($this->ip,$this->port)) {
            //组装包头包体数据
            $data = Common::en_pack_body($data);
            $this->client->send($data);//发送数据
            $res = $this->client->recv();//接收数据
            //解析包头包体数据
            $res = Common::de_pack_body($res);
            return $res;
        }

    }

}

