<?php
/**
 * Created by PhpStorm.
 * User: dream
 * Date: 2018/5/19
 * Time: 下午4:51
 */

namespace home;


use home\common\Common;

class autoloader
{
    /**
     * 项目根目录名称
     * @var string
     */
    protected static $_root_dirname = 'home';

    /**
     * 自动加载根路劲
     * @var string
     */
    protected static $_autoload_root_path = '';

    /**
     * 设置自动加载根路劲
     * @param $root_path
     */
    public static function set_root_path($root_path)
    {
        self::$_autoload_root_path = $root_path;
    }

    /**
     * 根据类名加载文件
     * @param $name
     */
    public static function load_by_namespace($name)
    {
        //替换命令空间路劲的反斜杠
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);

        //如果命名为项目根目录
        if (strpos($name, self::$_root_dirname . '\\') === 0) {
            //组装类文件路劲
            $class_file = __DIR__ . substr($class_path, strlen(self::$_root_dirname)) . '.php';
        } else {
            //如果有设置自动加载根路劲
            if (self::$_autoload_root_path) {
                //直接组装类文件路劲
                $class_file = __DIR__ .DIRECTORY_SEPARATOR . $class_path . '.php';
            }

            //如果没有类文件 或者 类文件没找到 则进入上级项目上级目录查找
            if (empty($class_file) || !is_file($class_file)) {
                $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "{$class_path}.php";
            }
        }

        if (is_file($class_file)) {
            //引入类文件
            require_once($class_file);
            //如果该类存在 则返回true  [第二参数 false 是阻止继续执行autoload]
            if (class_exists($name,false)) {
                return true;
            }
        } else {
            //这里做路劲加载错误抛出异常
            throw new \Exception('文件不存在');

            return true;
        }


        return false;
    }


}

//注册自动加载类
spl_autoload_register('\home\autoloader::load_by_namespace');