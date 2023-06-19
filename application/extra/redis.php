<?php
/**
 * Created by PhpStorm.
 * User: wangfanchang
 * Date: 18/1/10
 * Time: 下午3:05
 */
// Predis配置文件 wiki：https://github.com/nrk/predis

use think\Env;

return [
    'ip'=>Env::get('redis.ip','127.0.0.1'),
    'port'=>Env::get('redis.port','6379'),
    'password'=>Env::get('redis.user','mima'),
    'timeout' => Env::get('redis.timeout', 2), //超时时间 秒
    'pconnect' => Env::get('redis.pconnect', 0), //是否使用长连接 默认0不使用 1使用

];
