<?php
/**
 * Created by PhpStorm.
 * User: wangfanchang
 * Date: 18/1/10
 * Time: 下午2:13
 */

namespace app\common\library;

use app\common\service\LogService;
use think\Config;
use think\Log;

class Redis
{

    /**
     * 单例对象
     */
    protected static $instance;

    /**
     * Redis实例
     * @var null
     */
    protected static $connect = null;

    /**
     * @return \Redis
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function __construct()
    {
        if (is_null(self::$connect)){
            $ip = Config::get('redis.ip');
            $port = Config::get('redis.port');
            $timeout = Config::get('redis.timeout');
            $password = Config::get('redis.password');
            $redis  = new \Redis();
            $redis->connect($ip,$port,$timeout);
            $redis->auth($password);
            $redis->select(0);
            self::$connect = $redis;
        }

    }

    function __call($name, $arguments)
    {
        try{
            if (strtolower($name) == 'pfadd' && !is_array($arguments[1])) {
                $arguments[1] = [$arguments[1]];
            }

            if (strtolower($name) == 'delete') {
                $name = 'del';
            }
            Log::info('redis:'.$name.':'.json_encode($arguments));
            LogService::info();
            $ret = call_user_func_array([self::$connect, $name], $arguments);
            return $ret;
        }catch (\Exception $e){

        }
    }

}
