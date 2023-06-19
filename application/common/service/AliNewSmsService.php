<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\SDK\Sample;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;

class AliNewSmsService {

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Dysmsapi Client
     */
    public static function createClient($accessKeyId, $accessKeySecret){
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "youqibang.com";
        return new Dysmsapi($config);
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args){
        $client = self::createClient("LTAI5tGtaMpN6Eyu82rJxo9n", "ruKC9N78w0cqdnpn0JMSJBhdmG20ZT");
        $sendSmsRequest = new SendSmsRequest($args);
        // 复制代码运行请自行打印 API 的返回值
        $client->sendSms($sendSmsRequest);
    }



}
