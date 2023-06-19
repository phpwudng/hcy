<?php
/**
 * Created by PhpStorm.
 * User: wangfanchang
 * Date: 18/1/10
 * Time: 下午2:13
 */

namespace app\common\library;

class Ua
{
    /**
     * user agent
     */
    private static $ua = null;

    /**
     * 获取用户完整user agent
     * @return string
     */
    public static function getUa()
    {
        if (is_null(self::$ua)) {
            self::$ua = $_SERVER['HTTP_USER_AGENT'] ?? ''; //存在无UA的情况，如：微信callback post
        }
        return self::$ua;
    }

    /**
     * 获取NetType网络类型
     * @return string
     */
    public static function getNetType()
    {
        foreach (['WIFI', '4G', '3G', '2G'] as $key => $value) {
            if (stripos(self::getUa(), $value) !== false) {
                return $value;
            }
        }
        return 'Other';
    }
    
    /**
     * 获取OS环境（Ios，Android，Other）
     */
    public static function getOs()
    {
        if (self::isIos()) {
            return 'iOS';
        } elseif (self::isAndroid()) {
            return 'Android';
        } else {
            return 'Other';
        }
    }
    /**
     * 是否QQ环境
     * @return bool
     */
    public static function isQQ()
    {
        return stripos(self::getUa(), ' QQ/') !== false;
    }

    /**
     * 是否微信环境
     * @return bool
     */
    public static function isWeiXin()
    {
        return stripos(self::getUa(), 'MicroMessenger') !== false;
    }

    /**
     * 是否Android环境
     * @return bool
     */
    public static function isAndroid()
    {
        return stripos(self::getUa(), 'Android') !== false;
    }

    /**
     * 是否Ios环境
     * @return bool
     */
    public static function isIos()
    {
        return preg_match("/iPhone|iPad|iPod/i",self::getUa());
    }

    /**
     * 是否hua wei 手机环境
     * @return bool
     */
    public static function isHuaWei()
    {
        return preg_match("/build\/huawei|build\/honor|build\/hdh/i", self::getUa());
    }

    /**
     * 判断是不是手机端
     * @return bool
     */
    public static function isMobile()
    {
        //正则表达式,批配不同手机浏览器UA关键词。
        $regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
        $regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
        $regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
        $regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
        $regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320×320|240×320|176×220";
        $regex_match.=")/i";
        return preg_match($regex_match, self::getUa());
    }
}