<?php
namespace app\admin\service;

use app\common\service\LogService;
use fast\Http;
use think\Cache;

class YcService
{

    //chat_id : oc_b3290304f91e79e261521e42ec638ce1
    public static $appid = "yc_uHseU1zEW2Qs1QStn5t4b4JXb";
    public static $secret = "xp8DLW0mCZ1ya2W2jhlG7FyRvYj8";
    public static $url = "https://api.yc-client.anestcang.com";

    /**
     * curl -X POST -d '{"receive_id": "wusheng003","msg_type": "text","content": "{\"text\":\"test content\"}"}' --header 'Authorization: Bearer t-g1048aj4DGKEOGZUNU3TVDAQPBAWDB23QJ5UMPVE' --header 'Content-Type: application/json; charset=utf-8' 'https://open.feishu.cn/open-apis/im/v1/messages?receive_id_type=user_id'
     */
    public static function sendMessage($message = "今日销售额 100W 元"){

        $uri = self::$url."/im/v1/messages?receive_id_type=chat_id";
        $text = [
            'text'=>$message
        ];
        $params = [
            'receive_id'=>'oc_b3290304f91e79e261521e42ec638ce1',
            'msg_type'=>'text',
            'content'=>json_encode($text)
        ];
        $params = json_encode($params,JSON_UNESCAPED_SLASHES);
        $token = self::getToken();
        echo("params:{$token}");
        echo("params:{$params}");
        $res = Http::post($uri,$params,[
            CURLOPT_HTTPHEADER => ["content-Type:application/json","charset=utf-8","Authorization:Bearer {$token}"],
        ]);
        echo("发送结果:{$res}");

    }

    /**
     * 获取token
     */
    public static function getToken()
    {
        $token = Cache::get("YC-TOKEN");
        if (empty($token)){
            $uri = self::$url."/api/openPlatform/authorization/login";
            $params =[
                'appKey'=>self::$appid,
                'appSecret'=>self::$secret
            ];
            echo("params:".json_encode($params));
            $res = Http::post($uri,json_encode($params,JSON_UNESCAPED_SLASHES),[
                CURLOPT_HTTPHEADER => ['content-Type: application/json'],
            ]);
            $resArr = json_decode($res,true);
            if ($resArr['state'] == '000001'){
                $token = $resArr['data']['token'];

                echo("RES_TOKEN:{$res}".PHP_EOL);
                Cache::set('YC-TOKEN',$token,3600);
            }
        }else{
            echo("CACHE_TOKEN:{$token}");
        }
        return $token;

    }

    /**
     * @return string
     */
    public static function checkStack($top=20)
    {

        $uri = self::$url."/api/openPlatform/stock/list";
        $params = [
            'warehouseCode'=>'022',
            'page'=>1,
            'prePage'=>100,
        ];
        if (!empty($sku)){
            $params['customerSku'] = $sku;
        }
        $token = self::getToken();
        echo("params:{$token}");
        $message = "";
        while (true){
            echo($params['page'].PHP_EOL);
            $res = Http::POST($uri,json_encode($params,JSON_UNESCAPED_SLASHES),[
                CURLOPT_HTTPHEADER => ["Content-Type:application/json","charset=utf-8","Authorization: Bearer {$token}"],
            ]);
            $res = json_decode($res,true);
            if ($res['state'] == "000001"){
                if (empty($res['data']['list'])){
                    break;
                }
                foreach ($res['data']['list'] as $item){
                    if($item['available'] < $top && $item['available'] != 0){
                        $message .= "SKU:".$item['customerSkuName'].",库存:".$item['available'].PHP_EOL;
                    }
                }
            }else{
                echo("获取库存异常:".json_encode($res));
                break;
            }
            usleep(400000);
            $params['page']++;
        }
        if ($message){
            $pre = "库存低于{$top}的SKU:".PHP_EOL;
            $message = $pre.$message;
        }
        echo($message);
        return $message;

    }

    public static function testSign()
    {
        $uri = self::$url . "/api/openPlatform/apiMsg/signTest";
        $params = [
            "msgType" => "IN_ORDER",
            "data" => [
                "for" => "bar"
            ]
        ];
        $token = self::getToken();

        $res = Http::POST($uri,json_encode($params,JSON_UNESCAPED_SLASHES),[
            CURLOPT_HTTPHEADER => ["Content-Type:application/json","charset=utf-8","Authorization: Bearer {$token}"],
        ]);
        var_dump($res);
    }
}