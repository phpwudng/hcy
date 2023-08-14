<?php

namespace app\admin\service;

use app\common\service\LogService;
use fast\Http;
use Monolog\Handler\IFTTTHandler;
use think\Cache;

class WxgjService
{

    public static $url = "https://wxgj.shopeeok.com/";

    /**
     * 获取token
     */
    public static function checkUnOrders()
    {
        $page = 1;
        $pageSize = 200;
        $uri = self::$url . "agent-foreign/order/list?_t=1692000117&dgStatus=2&orderStatus=pending&queryType=1&column=createTime&order=desc&field=id,&pageNo={$page}&pageSize={$pageSize}";
        $header = [
            "Content-Type:application/json;charset=UTF-8",
            "Set-Cookie:_ati=5636080998074;SERVERID=1cca1c029ff510acf0d6bc5315a401b9|1692000068|1691999452",
            "Referer:https://wxgj.shopeeok.com/duiguo/order/manage/readytoship",
            "X-Access-Token:7e34f9dfd7934805991574c8a107e7d5",
        ];
        echo $uri . PHP_EOL;
        $res = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $resArr = json_decode($res, true);

        if ($resArr['code'] == 0) {
            $message002 = "以下订单请提醒厂家发货:".PHP_EOL;
            $preSend = false;
            foreach ($resArr['result']['records'] as $orders) {
                if (date("Ymd", strtotime($orders['createTime'])) == date("Ymd", time())) {
                    LogService::info("当日订单不提醒");
                    continue;
                }
                //发货提醒
                $sendDays = ceil((strtotime($orders['shipByDate']) - time()) / 86400);
                $createDays = ceil((time() - strtotime($orders['createTime'])) / 86400);
                if ($sendDays <= 5) {
                    $message = "台湾-{$orders['shopNote']},代发货提醒:" . PHP_EOL;
                    $message .= "订单号:" . $orders['ordersn'] . PHP_EOL;
                    $message .= "下单时间:" . $orders['createTime'] . PHP_EOL;
                    $message .= "最后发货时间:" . $orders['shipByDate'] . PHP_EOL;
                    $message .= "请注意，距离最后期限只剩下({$sendDays}天)";
                    if (!empty($orders['items'])) foreach ($orders['items'] as $item) {
                        if (empty($item['bindSysCode'])) {
                            $message .= "没有库存的SKU:" . $item['variationName'] . PHP_EOL;
                        }
                    }
                    FeiShuService::sendMessage($message);
                    sleep(1);
                }
                //催货提醒
                echo $createDays . PHP_EOL;
                if ($createDays > 5) {
                    $preSku = false;
                    if (!empty($orders['items'])) foreach ($orders['items'] as $item) {
                        if (empty($item['bindSysCode'])) {
                            $preSku = true;
                        }
                    }
                    if ($preSku) {
                        $preSend = true;
                        $message002 .= "订单号:{$orders['ordersn']},已经下单({$sendDays})天" . PHP_EOL;
                    }

                }

            }
            if ($preSend) {
                FeiShuService::sendMessage($message002);
            }
        }

    }

}