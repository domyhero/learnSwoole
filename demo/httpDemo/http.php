<?php
require_once dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'autoloader.php';
use learnswoole\common\common;
class http
{
    private $http;

    function __construct($config=[],$connectConfig=[])
    {
        $this->http = new swoole_http_server($connectConfig['servHost'],$connectConfig['servPort']);

        //设置
        $this->http->set([
            'package_max_length' => 1024*1024*10,//最大10兆
            'upload_tmp_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'upload',//文件上传的路劲
        ]);

        //注册事件
        $this->http->on('request',[$this,'onRequest']);

        //启动服务
        $this->http->start();
    }

    //监听http协议
    public function onRequest($request,$response)
    {
        //设置响应头 信息
        $response->header('Content-Type','text/html');
        $response->header('Charset','utf-8');

        //获取get信息  $_GET
//        common::dump($request->get);

        //获取post信息
//        common::dump($request->post);

        //$_SERVER 信息
//        common::dump($request->server);

        //获取头部信息
//        common::dump($request->header);

        //根据请求头的不同类型,返回响应格式的数据 使用与微信开发场景等
        if (isset($request->header['content_type']) && $request->header['content_type'] == 'application/json') {
            common::dump('接收到json格式数据');
        } elseif (isset($request->header['content_type']) && $request->header['content_type'] == 'application/xml') {
            common::dump('接收到xml数据');
        }

        //接收到文件上传 , 这里不支持大文件
        $file = $request->files;
//        common::dump($request->rawContent());//获取原始数据 php://input
//        common::dump($file);
//        move_uploaded_file($file['dream']['tmp_name'],__DIR__ . '/upload/a.jpg');

        //响应状态
//        $response->status(200);

        //设置响应cookie
//        $response->cookie('user','dream');

        //分段发送不能使用end
//        $response->write('hahah ------');
//        $response->write('999');

        //响应上传文件
//        $response->sendfile(__DIR__.'/upload/a.jpg');


//        $response->end(common::html_echo("<h1> get  id : {$request->post['id']} , name : {$request->post['name']} </h1>"));


        //获取请求的server信息  即 $_SERVER 变量
        $server = $request->server;

        //获取请求网址路由信息
        $path_info = $server['path_info'];
        //如果没有路由地址 则默认为 '/'
        if ($path_info == '/') {
            $path_info = '/';
        } else {
            $path_info = explode('/',$path_info);
        }

        if (!is_array($path_info)) {
            //如果没有请求地址 则默认返回404码
            $response->status(404);
            //返回html 代码需要声明头部
            $response->end('<meta charset="UTF-8">请求路径无效');
        }

        //获取模块 有则返回 无则返回 默认页面
        $model = (isset($path_info[1]) && !empty($path_info[1])) ? $path_info[1] : 'Home';

        //获取控制器路劲
        $controller = (isset($path_info[2]) && !empty($path_info[2])) ? $path_info[2] : 'Index';

        //方法
        $method = (isset($path_info[3]) && !empty($path_info[3])) ? $path_info[3] : 'index';

        //结合错误处理
        try {
            $class_name = "\\{$model}\\{$controller}";
            $obj = new $class_name;
        } catch (\Exception $e) {
            $response->status(200);
            $response->end('<meta charset="UTF_8">'.$e->getMessage());
        }


    }


}

register_shutdown_function('\learnswoole\common\common::handleFatal');

new http(common::server_tcp_configs(),common::connect_config());

