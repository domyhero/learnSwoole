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

new http(common::connect_config());