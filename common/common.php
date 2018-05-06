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
            echo $data.PHP_EOL;
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

}