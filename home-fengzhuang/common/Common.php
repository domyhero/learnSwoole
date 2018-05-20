<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/19
 * Time: 下午4:58
 */
namespace home\common;

class Common
{
    protected $config;
    protected static $log_path;

    public function __construct()
    {
        $this->config = include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config'.DIRECTORY_SEPARATOR."config.php";
        //存放 http 日志文件地址
        self::$log_path = $this->config['http']['log_path'];
    }

    /**
     * cli 打印输出
     * @param $data
     */
    public static function dump($data)
    {
        if (is_string($data)) {
            echo $data . PHP_EOL;
        } else {
            var_dump($data);
            echo PHP_EOL;
        }
    }

    /**
     * tcp包头包体配置 解决粘包问题
     * @return array
     */
    public static function pack_body_config()
    {
        return array(
            'open_length_check' => true,    //打开包长检测
            'package_max_length' => 1024 * 1024 * 2,    //最大数据包尺寸 2兆 此属性设置越大占用内存越大
            'package_length_type' => 'N',   //长度值类型:   N 无符号、网络字节序、4字节
            'package_body_offset' => 4,     //从第几个字节开始计算长度 包体
            'package_length_offset' => 0,   //length长度值在包头的第几个字节。
        );
        
    }

    /**
     * 解析包头包体数据
     * @param $data
     * @return bool|mixed|string
     */
    public static function de_pack_body($data)
    {
        $len = unpack('N',$data)[1];
        $body = substr($data,-$len);//去除二进制数据之后,不要包头的数据
        if (self::is_json($body)) {
            $body = json_decode($body, true);
        }
        return $body;

    }

    /**
     * 组装包头包体数据
     * @param $data
     * @return string
     */
    public static function en_pack_body($data)
    {
        //如果是数组则进行json
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $packge = pack('N', strlen($data)) . $data;
        return $packge;
    }

    /**
     * 验证字符串是否是json格式
     * @param $str  需要验证的字符串
     * @return bool 是返回 true 不是 返回false
     */
    public static function is_json($str)
    {
        //接收解析字符串
        json_decode($str);
        //判断是否是合法的json字符串  这里 整型 123456 也是合法的 所有需要正则匹配验证
        if (json_last_error() == JSON_ERROR_NONE) {
            $json_str = str_replace('\\', '', $str);
            preg_match('/{.*}/', $json_str, $out_arr);
            if (!empty($out_arr)) {
                return true;
            }
        }
        return false;
    }


    /**
     * 进程关闭时触发此信息函数,或者发送错误信息到某个设备,发送邮件等行为
     * 使用需要加载          register_shutdown_function('\home\common\common::handleFatal');
     */
    public static function handleFatal()
    {
        $error = error_get_last(); //获取最后的错误
        if (isset($error['type']))
        {
            switch ($error['type'])
            {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $message = $error['message'];
                    $file = $error['file'];
                    $line = $error['line'];
                    $log = "$message ($file:$line)\nStack trace:\n";
                    $trace = debug_backtrace();
                    foreach ($trace as $i => $t)
                    {
                        if (!isset($t['file']))
                        {
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line']))
                        {
                            $t['line'] = 0;
                        }
                        if (!isset($t['function']))
                        {
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object']))
                        {
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                    if (isset($_SERVER['REQUEST_URI']))
                    {
                        $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                    }
                    //捕获并存储致命错误日志
                    file_put_contents(self::$log_path,$log,FILE_APPEND);
                default:
                    break;
            }
        }
    }

}


