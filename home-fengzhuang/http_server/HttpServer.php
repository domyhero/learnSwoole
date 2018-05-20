<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/20
 * Time: 下午3:35
 */

namespace home\http_server;


use home\autoloader;
use home\common\Common;

class HttpServer extends Common
{
    public $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new \swoole\http\server('0.0.0.0',$this->config['http']['port']);
        $this->http->set($this->config['http']['set']);

        //注册事件
        $this->http->on('request',[$this,'onRequest']);

        //注册专用 捕获致命错误 友好返回客户端
        register_shutdown_function('\home\common\common::handleFatal');

        $this->http->start();
    }

    public function onRequest($request,$response)
    {
        //设置
        $response->header('Content-Type','text/html');
        $response->header('Charset','utf-8');
        //获取$_SERVER 信息
        $server=$request->server;
        //获取请求路劲
        $path_info=$server['path_info'];
        if ($path_info == '/favicon.ico') {
            //如果是请求 ico 直接返回
            return ;
        }
        //获取请求路径
        if($path_info == '/'){
            $path_info = '/';
        } else {
            $path_info = explode('/',$path_info);
        }
        $endsign = true;
        $index = true;
        if (!is_array($path_info)) {
            $response->status(404);//返回404页面不存在状态
            $response->sendfile(__DIR__.DIRECTORY_SEPARATOR.'show'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'404.html');
            //结束处理标识
            $endsign = false;
        } else {
            //进行非法地址验证 返回404
            $path_count = count($path_info);
            if ($path_count > 3) {
                foreach ($path_info as $k => $v) {
                    if ($k > 0 && $k < ($path_count - 2)) {
                        //匹配是否含有 . 符号
                        if (strpos($v,'.') !== false) {
                            $response->status(404);//返回404页面不存在状态
                            $response->sendfile(__DIR__.DIRECTORY_SEPARATOR.'show'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'404.html');
                            $endsign = false;
                            break;
                        }
                    }
                }
            } else {
                if ($path_count[1] == 'index.php') {
                    $model = 'Show';                        //默认初始值
                    $controller = 'Show';                   //默认初始值
                    $method = 'index';                      //默认初始值
                    $index = false;
                } else {
                    $response->status(404);//返回404页面不存在状态
                    $response->sendfile(__DIR__.DIRECTORY_SEPARATOR.'show'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'404.html');
                    $endsign = false;
                }
            }
        }
        //此处用 标识来确定是否执行 因为 sendfile 执行后不能 再执行任何其他输出
        if ($endsign) {
            if ($index) {
                //获取模块
                $model = (isset($path_info[1]) && !empty($path_info[1])) ? $path_info[1] : 'Show';          //默认初始值
                //控制器
                $controller = (isset($path_info[2]) && !empty($path_info[2])) ? $path_info[2] : 'Show';     //默认初始值
                //方法
                $method = (isset($path_info[3]) && !empty($path_info[3])) ? $path_info[3] : 'index';        //默认初始值
            }

            //注册http_server的自动加载类

            $class_name = "home\\http_server\\{$model}\\controller\\{$controller}";
            //结合错误处理
            try {
                $obj = new $class_name;
                $res = $obj->$method();
                $response->end($res);
            } catch (\Exception $e) {
                $response->status(404);
                //此处调用 404 页面  返出
                $response->sendfile(__DIR__.DIRECTORY_SEPARATOR.'show'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'404.html');
//            $response->end('<meta charset="UTF-8">'.$e->getMessage());
            }
        }
    }
}



