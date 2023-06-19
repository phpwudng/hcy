<?php
/**
 * Created by PhpStorm.
 * User: Bear
 * Date: 2018/10/23
 * Time: 上午10:28
 */

namespace app\common\service;

use think\Log;

class LogService
{
    /**
     * 细节级别
     * @param $msg
     */
    public static function debug($msg)
    {
        self::log($msg, 'debug');
    }

    /**
     * 了解级别
     * @param $msg
     */
    public static function info($msg)
    {
        self::log($msg, 'info');
    }

    /**
     * 通知级别
     * @param $msg
     */
    public static function notice($msg)
    {
        self::log($msg, 'notice');
    }

    /**
     * 错误级别
     * @param $msg
     */
    public static function error($msg)
    {
        self::log($msg, 'error');
    }

    /**
     * 告警级别
     * @param string $msg
     */
    public static function alert($msg)
    {
        self::log($msg, 'alert');
    }

    /**
     * 紧急级别
     * @param string $msg
     */
    public static function critical($msg)
    {
        self::log($msg, 'alert');
    }

    /**
     * @param string $msg
     * @param string $level
     */
    public static function log($msg, $level)
    {
        $msg = "time:[ " . date('Y-m-d H:i:s') . " ]\t" . $msg;
        Log::record($msg, $level);
    }

    /**
     * @param \Exception $e
     * @return array
     */
    public static function exception(\Exception $e)
    {
        $msg = json_encode([
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
        ]);
        return self::toError($e->getCode(), $msg, 'critical');
    }

    /**
     * 格式化错误返回值
     * @param        $error_code
     * @param        $error_msg
     * @param string $level
     * @return array
     */
    public static function toError($error_code, $error_msg, $level = 'error')
    {
        if (is_array($error_msg)) {
            $error_msg = json_encode($error_msg);
        }
        $return = ['error' => $error_code, 'msg' => $error_msg];
        //将进程id写入日志，方便定位
        self::log("errcode:[ $error_code ]\terrmsg:[ $error_msg ]", $level);
        return $return;
    }

    /**
     * 格式化成功返回值
     * @param        $error_code
     * @param        $error_msg
     * @param string $level
     * @return array
     */
    public static function toSuccess($error_code, $error_msg, $level = 'debug')
    {
        if (is_array($error_msg)) {
            $error_msg = json_encode($error_msg);
        }
        $return = ['error' => $error_code, 'msg' => $error_msg];
        //将进程id写入日志，方便定位
        self::log("errcode:[ $error_code ]\terrmsg:[ $error_msg ]", $level);
        return $return;
    }
}