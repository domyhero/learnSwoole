<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/20
 * Time: 上午11:18
 */

return array(

    //全局Redis配置
    'redis' => array(
        'ip' => '127.0.0.1',                            //IP地址
        'port' => '6379',                               //端口
    ),

    //TCP 包头 + 包体 配置  解决粘包问题
    'body_pack_config' => array(
        'open_length_check' => true,                    //打开包长检测
        'package_max_length' => 1024 * 1024 * 2,        //最大数据包尺寸 2兆 此属性设置越大占用内存越大
        'package_length_type' => 'N',                   //长度值类型:   N 无符号、网络字节序、4字节
        'package_body_offset' => 4,                     //从第几个字节开始计算长度 包体
        'package_length_offset' => 0,                   //length长度值在包头的第几个字节。
    ),

    //web端代理服务器配置 -> 配合apache nginx 使用 即 访问的路劲或文件不存在 则向次代理服务器请求 配置 在底下
    'http' =>array(
        'ip' => '127.0.0.1',                            //代理服务器IP
        'port' => 8888,                                 //代理服务器端口
        'set' => array(                                 //代理服务器 服务端配置
            'package_max_length'=>1024*1024*10,         //请求最大 10M
            'enable_static_handler' => true,            //排除静态文件
            'document_root' => dirname(__DIR__).DIRECTORY_SEPARATOR.'http_server'.DIRECTORY_SEPARATOR.'static',       //静态文件目录 , css/js/images
        ),
        'log_path' => dirname(__DIR__).DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'http.log'     //web端请求错误日志,此方法 swoole专用捕获致命错误,其他的方法不行
    ),

    //rpc服务中心配置 多端口混合协议监听
    'rpc' => array(
        'ip' => '47.90.102.215',                        //rpc IP地址
        'websocket_port' => 9800,                       //rpc 监听的websocket端口
        'websocket_set' => array(                       //rpc websocket 设置配置参数
            'worker_num'=>6,                            //开启6个工作进程
            'package_max_length'=>1024*1024*10,         //最大请求长度 10兆
            'max_request'=>3000,                        //最大连接数  3000,用于防止 内存溢出,达到请求后 自动重启,释放溢出内存
            'heartbeat_idle_time'=>5,                   //连接最大的空闲时间
            'heartbeat_check_interval'=>2               //定时检测在线列表
        ),
        'tcp_port' => 9801,                             //rpc 监听tcp协议端口
        'tcp_set' => array(                             //rpc tcp端口设置
            'worker_num' => 3,                          //开启3个工作进程
            'package_max_length'=>1024*1024*10,         //最大请求长度 10兆
            'max_request'=>3000,                        //最大连接数  3000,用于防止 内存溢出,达到请求后 自动重启,释放溢出内存
            'open_length_check' => true,                //打开包长检测
            'package_length_type' => 'N',               //长度值类型:   N 无符号、网络字节序、4字节
            'package_body_offset' => 4,                 //从第几个字节开始计算长度 包体
            'package_length_offset' => 0,               //length长度值在包头的第几个字节。
        ),
    ),

    //代码服务端配置
    'distributed' => array(
        //9503服务 比如 购物车服务
        'server_9503' => array(
            'serviceName' => 'CartService',             //当前服务 名称
            'ip' => '47.90.102.215',                    //服务端 IP地址
            'websocket_port' => 9503,                 //服务端 监听的 websocket端口
            'websocket_set' => array(                   //服务端 websocket配置
                'worker_num'=>6,                        //开启6个工作进程
                'package_max_length'=>1024*1024*10,     //最大请求长度 10兆
                'max_request'=>3000,                    //最大连接数  3000,用于防止 内存溢出,达到请求后 自动重启,释放溢出内存
            ),
        ),
    ),
);


/*
nginx + swoole 配置文件修改

server {
    root /data/wwwroot/;
    server_name local.swoole.com;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-e $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}



apache + swoole 配置文件修改

<VirtualHost *:80>
    ServerAdmin 123@qq.com
    php_admin_value open_basedir /data/www/home:/tmp:/var/tmp:/proc:/data/ww    w/default/phpmyadmin
    ServerName 127.0.0.1
    ServerAlias 127.0.0.1
    DocumentRoot /data/www/home
    <Directory /data/www/home>
        SetOutputFilter DEFLATE
        Options FollowSymLinks
        AllowOverride All
        Order Deny,Allow
        Require all granted
        DirectoryIndex index.php index.html index.htm
    </Directory>

    ## 重点 http.conf 中开启重写模块功能 然后增加 下面模块判断进行跳转
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_FILENAME} !-f
        RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ http://127.0.0.1:8888$1 [L,P]
    </IfModule>

    ErrorLog /data/wwwlog/127.0.0.1/error.log
    CustomLog /data/wwwlog/127.0.0.1/access.log combined
 /usr/local/apache/conf/vhost/127.0.0.1.conf[1]          unix utf-8 5:22/23
    ErrorLog /data/wwwlog/127.0.0.1/error.log
    CustomLog /data/wwwlog/127.0.0.1/access.log combined
</VirtualHost>


 */