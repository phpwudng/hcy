<?php


namespace app\common\service;


use app\common\library\Redis;
use GuzzleHttp\Client;

class WxService
{
    public $appid='wx5a696e57d88e2188';
    private $secret='65fbc4e71a5d8a19107c45f990ac8001';
    private $host = 'https://api.weixin.qq.com';
    protected static $instance;

    /**
     * @return static
     */
    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 获取微信token
     * @return false|mixed|string
     */
    public function getToken()
    {
        $uri = '/cgi-bin/token?grant_type=client_credential';
        $redisKey = 'WXTOKEN';
        $token = Redis::instance()->get($redisKey);
        if (! $token){
            $url = $this->host.$uri.'&appid='.$this->appid.'&secret='.$this->secret;
            $cient = new Client();
            $result = $cient->get($url);
            $res = $result->getBody()->getContents();
            $res = json_decode($res,true);
            if (! empty($res['access_token'])){
                $token = $res['access_token'];
                Redis::instance()->setex($redisKey,$res['expires_in']-10,$token);
            }
        }
        return $token;
    }


    /**
     * 获取jsapi ticket
     * @return false|mixed|string
     */
    public function getJsapiTicket()
    {
        $token = $this->getToken();
        if (empty($token)){
            return '';
        }
        $url = $this->host.'/cgi-bin/ticket/getticket?type=jsapi&access_token='.$token;
        $redisKey = 'WXJSAPI';
        $jsapiTicket = Redis::instance()->get($redisKey);
        if (! $jsapiTicket){
            $cient = new Client();
            $result = $cient->get($url);
            $res = $result->getBody()->getContents();
            $res = json_decode($res,true);
            if (! empty($res['ticket'])){
                $jsapiTicket = $res['ticket'];
                Redis::instance()->setex($redisKey,$res['expires_in']-10,$jsapiTicket);
            }
        }
        return $jsapiTicket;
    }

    /**
     * 获取jsapi的签名
     */
    public function getJsapiSign()
    {
        $time = time();
        $params = [
            'jsapi_ticket'=>$this->getJsapiTicket(),
            'noncestr'=>'Wm3WZYTPz0wzccnW',
            'timestamp'=>$time,
        ];
        $paramsStr = http_build_query($params);
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $paramsStr .='&url='.$url;
        $signatrue = sha1($paramsStr);


        return ['noncestr'=>$params['noncestr'],'timestamp'=>$time,'signatrue'=>$signatrue,'shareUrl'=>$url];

    }
    /**
     * 获取jsapi的签名
     */
    public function getYqbJsapiSign()
    {
        $time = time();
        $params = [
            'jsapi_ticket'=>$this->getJsapiTicket(),
            'noncestr'=>'Wm3WZYTPz0wzccnW',
            'timestamp'=>$time,
        ];
        $paramsStr = http_build_query($params);
        $url = $_SERVER["HTTP_REFERER"];
        $paramsStr .='&url='.$url;
        $signatrue = sha1($paramsStr);


        return [
            'noncestr'=>$params['noncestr'],
            'timestamp'=>$time,
            'signature'=>$signatrue,
            'share_url'=>$url,
            'appid'=>$this->appid,
            'share_img'=>\think\Config::get('site.yqb_share_img')
        ];

    }
}