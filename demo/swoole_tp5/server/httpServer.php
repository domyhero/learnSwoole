<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/12
 * Time: 下午5:15
 */

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'autoloader.php';

use learnswoole\common\common;
use learnswoole\common\tcpPack;
use think\Container;  //加载 think核心实例

class httpServer
{
    //服务端对象
    private $http;
    public function __construct($config = [], $connectConfig = [])
    {
        //全局对象
        $this->http = new \swoole\http\server($connectConfig['servHost'],$connectConfig['servPort']);

        //配置
        $this->http->set($config);


        //注册事件
        //工作进程
        $this->http->on('workerStart',[$this,'onWorkerStart']);
        //收到一个完整的Http请求后
        $this->http->on('request',[$this,'onRequest']);

        //启动服务
        $this->http->start();

    }

    //监听http协议
    public function onRequest($request, $response)
    {

        //将请求的$_SERVER 信息传递给框架 , 并根据框架进行处理 比如大小写转换  都要转换
        if (isset($request->server)) {
            foreach ($request->server as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
        }
        //header参数放在server参数
        if (isset($request->header)) {
            foreach ($request->header as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
        }

        unset($_GET);
        if (isset($request->get)) {
            foreach ($request->get as $key => $value) {
                $_GET[strtoupper($key)] = $value;
            }
        }
        if (isset($request->post)) {
            foreach ($request->post as $key => $value) {
                $_POST[strtoupper($key)] = $value;
            }
        }
        if (isset($request->cookie)) {
            foreach ($request->cookie as $key => $value) {
                $_COOKIE[strtoupper($key)] = $value;
            }
        }
        if (isset($request->files)) {
            foreach ($request->files as $key => $value) {
                $_FILES[strtoupper($key)] = $value;
            }
        }


        //处理谷歌ico请求
        if ($_SERVER['PATH_INFO'] == '/favicon.ico') {
            return ;
        }


        // 支持事先使用静态方法设置Request对象和Config对象



//        //设置响应头 信息
//        $response->header('Content-Type','text/html');
//        $response->header('Charset','utf-8');
        // 有请求时才执行应用并响应
        // 开启缓冲区 用户存放echo 的内容
        ob_start(); //

        //捕获异常 返回404 页面
        try {
            Container::get('app')->run()->send();
        } catch (\Exception $exception) {
            //返回404 等处理
            echo $exception->getMessage();
        }
        $res = ob_get_contents(); //获取缓冲区的内容
        //清空缓冲区
        ob_end_clean();
        //用http响应客户端
        $response->end($res);
    }

    //工作进程启动
    public function onWorkerStart($serv)
    {

        //注意taskWorker 启动不需要启动框架  可以使用共享
        //判断不是taskworker 才加载框架
        if (!$serv->taskworker) {
            // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php';

        }

        //实例化每个进程的Redis
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);

        //超全局变量存放 redis对象
        $GLOBALS['redis'] = $redis;




    }

    //清除worker进程redis 事件  kill -USR1

}

$config = [
    'worker_num' => 3,
    'max_request' => 1000,//最大请求 后worker进程重启,进行防备内存爆掉,释放内存
    'package_max_length' => 1024*1024*10,
    'upload_tmp_dir' => __DIR__ .DIRECTORY_SEPARATOR . '/upload'
];


new httpServer($config,common::connect_config());