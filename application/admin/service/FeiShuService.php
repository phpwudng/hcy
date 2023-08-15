<?php
namespace app\admin\service;

use app\common\service\LogService;
use fast\Http;
use think\Cache;

class FeiShuService
{

    //chat_id : oc_b3290304f91e79e261521e42ec638ce1
    public static $appid = "cli_a407f1a8d9bc500c";
    public static $secret = "ITGWTzLduDTk3yhPckbmCfUFYcdiwg6L";
    public static $url = "https://open.feishu.cn/open-apis";

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
        $token = Cache::get("TOKEN");
        if (empty($token)){
            $uri = self::$url."/auth/v3/tenant_access_token/internal";
            $params =[
                'app_id'=>self::$appid,
                'app_secret'=>self::$secret
            ];
              echo("params:".json_encode($params));
            $res = Http::post($uri,json_encode($params,JSON_UNESCAPED_SLASHES),[
                CURLOPT_HTTPHEADER => ['content-Type: application/json; charset=utf-8'],
            ]);
            $resArr = json_decode($res,true);
            if ($resArr['code'] == 0){
                $token = $resArr['tenant_access_token'];

                  echo("RES_TOKEN:{$res}".PHP_EOL);
                Cache::set('TOKEN',$token,3600);
            }
        }else{
              echo("CACHE_TOKEN:{$token}");
        }
        return $token;

    }

    /**
     * curl -i -X GET 'https://open.feishu.cn/open-apis/im/v1/chats' -H 'Authorization: Bearer t-g1048aj4DGKEOGZUNU3TVDAQPBAWDB23QJ5UMPVE'
     * @param $userId
     */
    public static function getChatId($userId)
    {

        $uri = self::$url."/im/v1/chats";
        $params = [
            'user_id_type'=>'user_id',
            'owner_id'=>$userId
        ];
        $params = json_encode($params,JSON_UNESCAPED_SLASHES);
        $token = self::getToken();
          echo("params:{$token}");
          echo("params:{$params}");
        $res = Http::get($uri,$params,[
            CURLOPT_HTTPHEADER => ["Content-Type:application/json","charset=utf-8","Authorization: Bearer {$token}"],
        ]);
          echo("发送结果:{$res}");

    }
}