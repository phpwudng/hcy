<?php
/**
 * Created by PhpStorm.
 * User: Elton
 * Date: 2020/7/16
 * Time: 18:23
 */

namespace app\common\service;


use app\common\library\Log;
use app\common\library\Redis;
use think\Config;

class SmsService
{
    protected static $instance;

    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 发送短信验证码
     * @param $phone 手机号
     * @return int
     */
    public function sendShortMsg($phone)
    {
        $code = AliSmsService::instance()->generateSMSCode();
        try {
            $data = AliSmsService::instance()->aliSendCode($phone, $code);
            if ($data['code']) {
                Redis::instance()->setex('MB:'.$phone,600,$code);
                return $code;
            } else {
                \think\Log::error('手机验证发送失败:' . $data['code']);
                return false;
            }
        } catch (\Exception $e) {
            LogService::error($e->getMessage());
            return false;
        }
    }

    /**
     * 检查验证码
     * @param $phone
     * @param $code
     * @return bool
     */
    public function check($phone,$code)
    {
        $realCode = Redis::instance()->get('MB:'.$phone);
        if (empty($realCode)){
            return false;
        }
        if ($realCode == $code){
            return true;
        }
        return false;

    }

    /**
     * 刷新验证码
     * @param $phone
     * @return bool
     */
    public function flush($phone)
    {
        Redis::instance()->del('MB:'.$phone);
        return true;
    }

}
