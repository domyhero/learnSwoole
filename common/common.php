<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/5
 * Time: 下午11:21
 */

namespace learnswoole\common;

/**
 * 公共方法类
 * Class common
 * @package learnswoole\common
 */
class common
{
    //友好输出
    public static function dump($data)
    {
//        echo PHP_EOL."输出内容如下: ".PHP_EOL;
        if (is_string($data)) {
            echo $data . PHP_EOL;
        } else {
            var_dump($data);
            echo PHP_EOL;
        }

    }

    //验证是否是合法的json数据
    public static function is_json($str)
    {
        //接送解析字符串
        json_decode($str);
        //判断是否是合法的接送字符串  这里 只要是字符串 '111' 也是合法的所以 需要继续验证
        if (json_last_error() == JSON_ERROR_NONE) {
            $json_str = str_replace('＼＼', '', $str);
            //匹配是否含有正确格式的json数据
            preg_match('/{.*}/', $json_str, $out_arr);
            if (!empty($out_arr)) {
                //如果有则返回true
                return true;
            }
        }
        return false;
    }

    //服务端swoole配置
    public static function server_tcp_configs()
    {
        $configs = [
            'worker_num' => 4,              //worker进程数量
            'reactor_num' => 4,             //reactor线程数量
            'task_worker_num' => 4,         //task_worker进程数量
            'max_request' => 1000,          //worker进程最大任务数,用于解决PHP进程内存溢出问题
            //'daemonize' => true,            //开启守护进程
            //'log_file' =>__DIR__ . 'server.log',    //记录server日志
            //'heartbeat_check_interval' =>60,    //启动心跳检测 60秒检测一次
            //'heartbeat_idle_time' => 600,       //最大允许连接空闲时间 超过则强制关闭该连接
            'open_length_check' => true,    //打开包长检测
            'package_max_length' => 1024 * 1024 * 2,    //最大数据包尺寸 2兆 此属性设置越大占用内存越大
            'package_length_type' => 'N',   //长度值类型:   N 无符号、网络字节序、4字节
            'package_body_offset' => 4,     //从第几个字节开始计算长度 包体
            'package_length_offset' => 0,   //length长度值在包头的第几个字节。
        ];
        return $configs;
    }

    //客户端配置
    public static function client_tcp_configs()
    {
        $configs = [
            'open_length_check' => true,    //打开包长检测
            'package_max_length' => 1024 * 1024 * 2,    //最大数据包尺寸 2兆 此属性设置越大占用内存越大
            'package_length_type' => 'N',   //长度值类型:   N 无符号、网络字节序、4字节
            'package_body_offset' => 4,     //从第几个字节开始计算长度 包体
            'package_length_offset' => 0,   //length长度值在包头的第几个字节。
        ];
        return $configs;
    }

    //客户端/服务端 连接配置
    public static function connect_config($name=null)
    {
        $connectConfig = [
            'servHost' => '0.0.0.0',
            'servPort' => 9501,
            'servMode' => SWOOLE_PROCESS,
            'servSockType' => SWOOLE_SOCK_TCP,
            'cliHost' => '127.0.0.1',
            'cliPort' => 9501,
            'cliSockType' => SWOOLE_SOCK_TCP,
            'cliAsync' => SWOOLE_SOCK_ASYNC,
        ];
        if (!empty($name)) {
            isset($connectConfig[$name]) || exit('ERROR : you can input servHost|servPort|servMode|servSockType|cliHost|cliPort|cliSockType|cliAsync  String');
            return $connectConfig[$name];
        }
        return $connectConfig;
    }

}