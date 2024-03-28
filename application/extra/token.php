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
    'type'=>'redis',
    'host'=>Env::get('redis.ip','127.0.0.1'),
    'port'=>Env::get('redis.port','6379'),
    'password'=>Env::get('redis.user','Aa123456'),
    'timeout' => Env::get('redis.timeout', 2), //超时时间 秒

];
