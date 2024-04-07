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
     * 同步所有订单
     */
    public static function getAllOrders($path)
    {
        $page = 1;
        $pageSize = 100;
        $inserts = [];
        $path .= "&pageNo={$page}&pageSize={$pageSize}";
        while (true){
            $tryNum = 3;
            $uri = self::$url . $path;
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
                        'shop_id'=>$orders['shopId'],
                    ];
                    $inserts[] = $temp;
                }
            }else{
                break;
            }
            foreach ($inserts as $order){
                $find = Db::table('orders_track')->where('orders_id',$order['orders_id'])->find();
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


    public static function checkStack($arr)
    {

        $page = 1;
        $pageSize = 200;

        $msg = "以下SKU存在多个链接，库存不足(<=2):" . PHP_EOL;
        while (true) {
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
                    if (in_array($sku['sku'],$arr) && $sku['remainNum'] <= 2){
                        $msg .= "SKU:{$sku['variationName']},剩余库存:{$sku['remainNum']},7天销售额:{$sku['weekSales']}" . PHP_EOL;
                    }
                }

            }
            $page++;
            usleep(500000);
        }
        if (strlen($msg) > 100) {
            FeiShuService::sendMessage($msg);
        }


    }



}