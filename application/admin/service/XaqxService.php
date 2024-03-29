<?php

namespace app\admin\service;

use app\common\library\Log;
use app\common\service\LogService;
use fast\Http;
use GuzzleHttp\Client;
use Monolog\Handler\IFTTTHandler;
use think\Cache;
use think\Db;

class XaqxService
{

    public static $url = "https://xaqx.shopeeok.com/";

    public static $header = [
        "Content-Type:application/json;charset=UTF-8",
        "_ati=5636080998074; SERVERID=1cca1c029ff510acf0d6bc5315a401b9|1708950302|1708949412",
        "Referer:https://xaqx.shopeeok.com",
        "X-Access-Token:45e0aae1cab1409aa90f95dfe2dbec70",
    ];

    public static $headerArr = [
        "Content-Type"=>"application/json;charset=UTF-8",
        "Referer"=>"https://xaqx.shopeeok.com",
        "X-Access-Token"=>"45e0aae1cab1409aa90f95dfe2dbec70",
    ];

    public static function syncStore($id)
    {
        $uri = self::$url . "agent-foreign/shopee/shopeePushRule/doPush?ruleIds={$id}";
        $header = self::$header;
        echo $uri . PHP_EOL;
        $res = Http::post($uri, [], [
            CURLOPT_HTTPHEADER => $header,
        ]);
        echo "库存同步规则:{$id},{$res}";
        \think\Log::info("库存同步规则:{$id},{$res}");

    }

    public static function getRuleList()
    {
        $ids = [];
        $tryNum = 3;
        while (empty($ids) && $tryNum>0){
            $uri = self::$url . "/agent-foreign/shopee/shopeePushRule/list";
            $header = self::$header;
            LogService::info($uri . PHP_EOL);
            $res = Http::get($uri, [], [
                CURLOPT_HTTPHEADER => $header,
            ]);
            $resArr = json_decode($res, true);

            echo $resArr['result']['total'].PHP_EOL;
            if ($resArr['code'] == 200 && $resArr['result']['total']){
                foreach ($resArr['result']['records'] as $item){
                    if ($item['pushStatus'] != 1){
                        LogService::info("推送任务没有开启:{$item['id']}");
                        continue;
                    }
                    $ids[] = $item['id'];
                }
            }
            $tryNum--;
        }
        if (!empty($ids)){
            foreach ($ids as $id){
                self::syncStore($id);
                sleep(30);
            }
        }
        LogService::info("库存同步完成");
    }

    /**
     * 获取所有有库存的本地商品ID
     */
    public static function getStoreSku()
    {
        $page = 1;
        $pageSize = 100;
        $skus = [];
        while (true){
            $uri = self::$url . "agent-foreign/shopee/userStock/list?_t=1711338947&column=remainNum&order=desc&field=id,,,wareHouse,undefined,categoryName,volume,remainNum,availableNum,frozenNum,day-minstock,count-sales,onTheWayNum,cost-total,action&pageNo={$page}&pageSize={$pageSize}";
            echo $uri . PHP_EOL;
            $res = Http::get($uri, [], [
                CURLOPT_HTTPHEADER => self::$header
            ]);
            $resArr = json_decode($res, true);

            if ($resArr['success'] === true && !empty($resArr['result']['page']['records'])){
                foreach ($resArr['result']['page']['records'] as $item){
                    if ($item['availableNum'] > 0 && $item['sku']){
                        $skus[] = $item['sku'];
                    }
                }
            }else{
                break;
            }
            sleep(1);
            $page++;
        }
        return $skus;

    }

    public static function setRuleByLocalProduct($storeSku)
    {
        $page = 1;
        $pageSize = 50;
        while (true){
            $uri = self::$url."agent-foreign/product/local/list?_t=1711359767&productType=0&column=createTime&order=desc&field=id,,sysCode,image,variationName,shopInfo,volume,cost,action&pageNo={$page}&pageSize={$pageSize}";
            echo $uri . PHP_EOL;
            $res = Http::get($uri, [], [
                CURLOPT_HTTPHEADER => self::$header
            ]);
            $ids = [];
            $resArr = json_decode($res, true);
            if ($resArr['success'] === true && !empty($resArr['result']['records'])){
                foreach ($resArr['result']['records'] as $item){
                    if (!empty($item['sku']) && in_array($item['sku'],$storeSku,true)){
                        $ids[] = $item['id'];
                    }
                }
            }else{
                break;
            }
            if (!empty($ids)){
                $productIds = implode(",",$ids);
                self::addRule($productIds,$page);
            }
            sleep(1);
            $page++;
        }
    }

    public static function addRule($productIds,$page)
    {
        $uri = self::$url . "agent-foreign/shopee/shopeePushRule/add";

        $params = [
            'firstShopId'=>'1759870897442660354',
            'firstShopStatus'=>1,
            'firstShopStockNum'=>1,
            'localProductIds'=>$productIds,
            'minStockStatus'=>2,
            'minStockNum'=>null,
            'pushPercent'=>100,
            'ruleName'=>'自动生成规则-'.$page,
            'shopIds'=>'1759870897442660354,1759871280361644033',
            'warehouseIds'=>1757
        ];

        echo $uri.PHP_EOL;
        $option = [
            'json' => $params,
            'headers' => self::$headerArr,
        ];
        $client = new Client();
        $response = $client->post($uri,$option);
        $res = $response->getBody()->getContents();
        echo $res;
        \think\Log::info("创建结果:{$res}");
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
        $pageSize = 100;
        $inserts = [];
        while (true){
            $tryNum = 3;
            $uri = self::$url . "/agent-foreign/order/list?_t=1698238191&agoFlag=0&orderBy=2&column=createTime&order=desc&field=id,&dgStatus=allhandle&pageNo={$page}&pageSize={$pageSize}";
            $header = self::$header;
            echo $uri . PHP_EOL;
            $resArr = [];
            while ($tryNum > 0){
                try {
                    $res = Http::get($uri, [], [
                        CURLOPT_HTTPHEADER => $header,
                    ]);
                }catch (\Throwable $throwable){
                    echo "异常重试中...";
                }
                if (!empty($res)){
                    $resArr = json_decode($res, true);
                    break;
                }else{
                    echo "重试中...";
                }
                sleep(3);
                $tryNum--;
            }
            if ($resArr['success'] == true) {
                if (empty($resArr['result']['records'])){
                    var_dump($resArr);
                    break;
                }
                foreach ($resArr['result']['records'] as $orders) {
                    $temp = [
                        'orders_id'=> $orders['ordersn'],
                        'orders_status'=>$orders['orderStatusQuery'],
                        'orders_pay_date'=>$orders['deliveryTime'],
                        'orders_ship_date'=>$orders['shipByDate'],
                    ];
                    $inserts[] = $temp;
                }
            }else{
                var_dump($resArr);
                break;
            }
            foreach ($inserts as $order){
                $find = Db::table('orders_track')->find($order['orders_id']);
                if ($find){
                    Db::table('orders_track')->where('orders_id',$order['orders_id'])->update($order);
                }else{
                    Db::table('orders_track')->insert($order);
                }
            }
            $inserts = [];
            $page++;
            sleep(5);
        }

        return true;

    }


}