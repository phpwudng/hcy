<?php

namespace app\admin\service;

use app\common\service\LogService;
use fast\Http;
use Monolog\Handler\IFTTTHandler;
use think\Cache;

class XaqxService
{

    public static $url = "https://xaqx.shopeeok.com/";

    public static $header = [
        "Content-Type:application/json;charset=UTF-8",
        "_ati=5636080998074; SERVERID=1cca1c029ff510acf0d6bc5315a401b9|1708950302|1708949412",
        "Referer:https://xaqx.shopeeok.com/duiguo/seller/store/manage",
        "X-Access-Token:45e0aae1cab1409aa90f95dfe2dbec70",
    ];

    public static function syncStore()
    {
        $uri = self::$url . "agent-foreign/shopee/shopeePushRule/doPush?ruleIds=1760690488748486657";
        $header = self::$header;
        echo $uri . PHP_EOL;
        $res = Http::post($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $resArr = json_decode($res, true);
        var_dump($resArr);

    }

    /**
     * 获取token
     */
    public static function checkUnOrders()
    {
        $page = 1;
        $pageSize = 200;
        $uri = self::$url . "agent-foreign/order/list?_t=1692000117&dgStatus=2&orderStatus=pending&queryType=1&column=createTime&order=desc&field=id,&pageNo={$page}&pageSize={$pageSize}";
        $header = self::$header;
        echo $uri . PHP_EOL;
        $res = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $resArr = json_decode($res, true);

        if ($resArr['code'] == 0) {
            $message002 = "台湾-以下订单请提醒厂家发货:" . PHP_EOL;
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
                    echo $message;
                    FeiShuService::sendMessage($message);
                    sleep(1);
                }
                //催货提醒
                echo $sendDays . PHP_EOL;
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

    public static function checkStack()
    {

        $page = 1;
        $pageSize = 200;

        $msg = "库存不足(7天销售额超过库存的1/3):" . PHP_EOL;
        $moreMsg = "库存积压(7天销售额为0,库存超过50):" . PHP_EOL;
        $saleMsg = "建议清仓(30天销售额为0,库存低于5):" . PHP_EOL;
        while (true) {
            echo $page;
            $uri = self::$url . "/agent-foreign/shopee/userStock/list?_t=1692064280&column=createTime&order=desc&pageNo={$page}&pageSize={$pageSize}";
            $header = self::$header;
            $res = Http::get($uri, [], [
                CURLOPT_HTTPHEADER => $header,
            ]);
            $resArr = json_decode($res, true);
            if (empty($resArr['result']) || $resArr['result']['page']['current'] > $resArr['result']['page']['pages']) {
                echo "数据拉取完成:{$res}" . PHP_EOL;
                break;
            }
            if ($resArr['code'] == 200) {
                foreach ($resArr['result']['page']['records'] as $sku) {
                    if ($sku['weekSales'] > 0 && ($sku['remainNum'] <= 20 || intval($sku['remainNum'] / $sku['weekSales'])) < 5) {
                        $msg .= "编码:{$sku['sysCode']},名称:{$sku['name']}-{$sku['variationName']},库存:{$sku['remainNum']},7天销售额:{$sku['weekSales']}" . PHP_EOL;
                    }
                    if (empty($sku['weekSales']) && $sku['remainNum'] > 50) {
                        $moreMsg .= "编码:{$sku['sysCode']},名称:{$sku['name']}-{$sku['variationName']},库存:{$sku['remainNum']},7天销售额:{$sku['weekSales']}" . PHP_EOL;
                    }
                    if ($sku['remainNum'] > 0 && $sku['remainNum'] < 5 && $sku['monthSales'] == 0) {
                        $saleMsg .= "编码:{$sku['sysCode']},名称:{$sku['name']}-{$sku['variationName']},库存:{$sku['remainNum']}" . PHP_EOL;
                    }
                }

            }
            $page++;
            usleep(500000);
        }

        if (date("w", time()) == 0 && date('H', time()) > 20) {
            if (strlen($moreMsg) > 100) {
                FeiShuService::sendMessage($moreMsg);
            }
            if (strlen($saleMsg) > 100) {
                FeiShuService::sendMessage($saleMsg);
            }
        }
        if (strlen($msg) > 100) {
            FeiShuService::sendMessage($msg);
        }


    }

    /**
     *
     */
    public static function checkOrderNo($ordersNoMaps)
    {
        $uri = self::$url . "/agent-foreign/order/list?_t=1693463210&agoFlag=0&queryType=1&orderBy=2&pageNo=1&pageSize=200&dgStatus=3";
        $header = self::$header;
        $re = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $res = json_decode($re, true);
        if ($res['code'] == 0) {
            foreach ($res['result']['records'] as $item) {
                echo "订单ID:{$item['ordersn']}".PHP_EOL;
                if (isset($ordersNoMaps[$item['ordersn']])) {
                    $excelList = explode(",", $ordersNoMaps[$item['ordersn']]);
                    $wxgjList = array_column($item['expressList'], 'trackingNo');
                    $wxgjStr = import(",",$wxgjList);
                    if (count($excelList) == 1 && count($wxgjList) == 1) {
                        if ($excelList[0] == $wxgjList[0]){
                            echo "订单验证成功:,{$item['ordersn']},{$excelList[0]},{$wxgjList[0]}".PHP_EOL;
                            continue;
                        }
                    }
                     echo "需要人工验证:,{$item['ordersn']},{$ordersNoMaps[$item['ordersn']]},{$wxgjStr}".PHP_EOL;
                }
            }
        }else{
            echo "结果:{$re}";
        }

    }

    public static function getOnlineSku()
    {
        $file = "./sku.txt";
        $page = 1;
        $pageSize = 200;
        $temp="";
        while (true){
            $uri = self::$url."/agent-foreign/shopItem/list?_t=1697186219&column=createTime&order=desc&field=id,,undefined,shopInfo,sku-name,action&pageNo={$page}&pageSize={$pageSize}";
            $header = self::$header;
            $re = Http::get($uri, [], [
                CURLOPT_HTTPHEADER => $header,
            ]);
            $res = json_decode($re, true);
            $page++;
            if ($page > 2){
                break;
            }
            if ($res['code'] != 200 && empty($res['result']['records'])){
                break;
            }
            foreach ($res['result']['records'] as $items){
                if (strstr($items['shopName'],"生日")){
                    continue;
                }else{
                    $arr = [
                        $items['itemId'],
                        $items['itemName'],
                        $items['shopId'],
                        $items['id']."\t",
                    ];
                    if (!empty($items['skus'])){
                        foreach ($items['skus'] as $sku){
                            $skuArr = [
                                $sku['localId']."-001",
                                $sku['shopItemId']."\t",
                                $sku['image'],
                                $sku['id']."\t",
                                $sku['createTime'],
                                $sku["shopSku"],
                                $sku['variationName'],
                            ];
                            $temp = implode(",",array_merge($arr,$skuArr)).PHP_EOL;
                            file_put_contents($file,$temp,FILE_APPEND);
                        }
                    }
                }

            }

        }


    }

    public static function getStore()
    {
        $uri = self::$url."/agent-foreign/userStorageBill/list?_t=1697194622&storeStatus=1230&column=createTime&order=desc&field=id,&pageNo=1&pageSize=200";
        $header = self::$header;
        $re = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $res = json_decode($re, true);
        $file = 'store.txt';
        foreach ($res['result']['records'] as $item){
            $arr = [
                $item['createTime'],
                $item['storageBillCode']
            ];
            foreach ($item['skuItems'] as $sku){
                $skuArr = [
                    $sku['sysCode'],
                    $sku['num'],
                    $sku['image'],
                    $sku['variationName'],
                ];
                $temp = implode(",",array_merge($arr,$skuArr)).PHP_EOL;
                file_put_contents($file,$temp,FILE_APPEND);
            }
        }
    }
    /**
     * 获取token
     */
    public static function getAllOrders()
    {
        $page = 1;
        $pageSize = 200;
        $uri = self::$url . "/agent-foreign/order/list?_t=1698238191&agoFlag=0&queryType=1&orderBy=2&column=createTime&order=desc&field=id,&dgStatus=allhandle&pageNo={$page}&pageSize={$pageSize}";
        $header = self::$header;
        echo $uri . PHP_EOL;
        $res = Http::get($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        $resArr = json_decode($res, true);
        $ids = [];
        if ($resArr['code'] == 0) {
            foreach ($resArr['result']['records'] as $orders) {
                $ids[] = $orders['ordersn'];
            }
        }
        return $ids;

    }


}