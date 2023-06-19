<?php


namespace app\api\controller;


use app\common\service\WxService;

class Wxtoken
{
    public function gettoken()
    {
        $token = WxService::instance()->getToken();
        return json($token);
    }

    public function getjsapiticket()
    {
        $jsapiTicket = WxService::instance()->getJsapiTicket();
        return json($jsapiTicket);
    }
    public function getJsapiSign(){
        WxService::instance()->getJsapiSign();
    }
}