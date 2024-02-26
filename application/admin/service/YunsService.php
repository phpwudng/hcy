<?php

namespace app\admin\service;

use app\common\service\LogService;
use fast\Http;
use Monolog\Handler\IFTTTHandler;
use think\Cache;

class YunsService
{

    public static $urls = "https://kuajing.shopeeok.com/";

    public static $headers = [
        "Content-Type:application/json;charset=UTF-8",
        "Set-Cookie:_ati=5636080998074; SERVERID=1cca1c029ff510acf0d6bc5315a401b9|1698236885|1698236715",
        "Referer:https://kuajing.shopeeok.com/duiguo/order/manage/seller",
        "X-Access-Token:53c606fe20a44450880b829bf660a5a7",
    ];

    /**
     * 获取token
     */
    public static function getAllOrder()
    {
        $page = 1;
        $pageSize = 200;
        $uri = self::$urls . "agent-foreign/order/list?_t=1698236944&agoFlag=0&queryType=1&orderBy=2&column=createTime&order=desc&field=id,&dgStatus=allhandle&pageNo={$page}&pageSize={$pageSize}";
        $header = self::$headers;
        echo $uri . PHP_EOL;
        $res = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $resArr = json_decode($res, true);
        $maps = WxgjService::getAllOrders();
        if ($resArr['code'] == 0){
            foreach ($resArr['result']['records'] as $orders){
                if (isset($maps[$orders['ordersn']])){
                    echo "订单异常:".$orders['ordersn'].PHP_EOL;
                }
            }
        }

    }



}