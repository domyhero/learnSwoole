<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/6
 * Time: 下午2:05
 */

namespace learnswoole\core;
use learnswoole\common\common;

class welcome
{
    public function index()
    {
        return "欢迎您的到来!~ 牛逼了,不需要重启swoole和工作进程,而是直接修改业务区文件改变的信息";
    }

}