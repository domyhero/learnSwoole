<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/5
 * Time: 下午10:39
 */

namespace learnswoole\common;

use learnswoole\common\common;

/**
 * tcp包体+包体转换
 * Class tcpPack
 * @package learnswoole\common
 */
class tcpPack
{

    /**
     * 解析包头包体数据
     * @param $data
     * @return bool|mixed|string
     */
    public static function de_pack_body($data)
    {
        $len=unpack('N',$data)[1];
        $body=substr($data,-$len);//去除二进制数据之后,不要包头的数据
        if (common::is_json($body)) {
            $body = json_decode($body,true);
        }
        return $body;
    }

    /**
     * 组装包头包体数据
     * @param $data
     */
    public static function en_pack_body($data)
    {
        //是数组进行json
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $packge = pack('N',strlen($data)).$data;
        return $packge;
    }

}