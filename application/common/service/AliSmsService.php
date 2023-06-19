<?php
/**
 * Created by PhpStorm.
 * User: Elton
 * Date: 2021/1/13
 * Time: 11:13
 */

namespace app\common\service;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class AliSmsService
{
    const CODE_ERROR = 0;
    const CODE_SUCCESS = 1;
    const SEND_SUCCESS_CODE = 1000;
    protected static $instance;

    private $accessKeyId = "LTAI5tGtaMpN6Eyu82rJxo9n";
    private $accessSecret = "ruKC9N78w0cqdnpn0JMSJBhdmG20ZT";
    private $regionId = "cn-shanghai";
    private $smsHost = "dysmsapi.aliyuncs.com";
    private $signName = "惠创业";

    private $templateCode_v1 = "SMS_218655435"; // 安全验证码
    private $templateCode_v2 = "SMS_199790639"; // 异常登陆通知

    //短信服务模版：
    //模版名称: CPS安全验证码
    //模版CODE: SMS_199770869
    //模版内容: 您的验证码${code}，该验证码5分钟内有效，请勿泄漏于他人

    //模版名称: 异常登陆通知
    //模版CODE: SMS_199790639
    //模版内容: 异常登录,群组:${groupName},ID:${admin_id},账号:${username},昵称:${nickname},类型:${errno},消息:${errmsg}
    //变量属性: groupName-其他；admin_id-其他号码；username-其他；nickname-其他；errno-其他；errmsg-其他；

    public static function instance(){
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 阿里短信，发送验证码
     * @param $phone
     * @param $code
     * @return array
     * @throws ClientException
     */
    public function aliSendCode($phone, $code)
    {
        $data = [
            'code' => $code
        ];
        return $this->_sendMsg($phone, $this->templateCode_v1, json_encode($data));
    }

    /**
     * 发送异常登录消息通知
     * @param $phone
     * @param $groupName 群组
     * @param $admin_id  ID
     * @param $username  账号
     * @param $nickname  昵称
     * @param $errno     类型
     * @param $errmsg    消息
     * @return array
     * @throws ClientException
     */
    public function aliSendNotice($phone,$groupName,$admin_id,$username,$nickname,$errno,$errmsg){
        $data = [
            'groupName' => $groupName,
            'admin_id' => $admin_id,
            'username' => $username,
            'nickname' => $nickname,
            'errno' => $errno,
            'errmsg' => $errmsg,
        ];
        return $this->_sendMsg($phone, $this->templateCode_v2, json_encode($data));
    }

    /**
     * @param $phone  手机号
     * @param $templateCode  模版名称: CPS安全验证码 => 模版CODE：SMS_199770869 | 模版名称: 异常登陆通知 => 模版CODE：SMS_199790639
     * @param $templateParam  JSON格式
     * @return array
     * @throws ClientException
     */
    private function _sendMsg($phone, $templateCode, $templateParam)
    {
        LogService::info("AliSms _sendMsg params:" . json_encode(['phone' => $phone, 'templateparam' => $templateParam]));
        AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessSecret)
            ->regionId($this->regionId)
            ->asDefaultClient();
        $a = [
            'query' => [
                'RegionId' => $this->regionId,
                'PhoneNumbers' => $phone,
                'SignName' => $this->signName,
                'TemplateCode' => $templateCode,
                'TemplateParam' => $templateParam,
            ]
        ];
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host($this->smsHost)
                ->options([
                    'query' => [
                        'RegionId' => $this->regionId,
                        'PhoneNumbers' => $phone,
                        'SignName' => $this->signName,
                        'TemplateCode' => $templateCode,
                        'TemplateParam' => $templateParam,
                    ]
                ])
                ->request();
            $rst_arr=$result->toArray();
            LogService::info("AliSms result:" . $result);
            if ($rst_arr['Code'] == "OK") {
                return ['code' => self::CODE_SUCCESS, 'message' => $rst_arr['Message']];
            } else {
                return ['code' => self::CODE_ERROR, 'message' => "发送短信出错"];
            }
        } catch (ClientException $e) {
            LogService::error("AliSms error: Message:" . $e->getMessage() . ',code:' . $e->getCode() . ' ,Line:' . $e->getLine());
        } catch (ServerException $e) {
            LogService::error("AliSms error: Message:" . $e->getMessage() . ',code:' . $e->getCode() . ' ,Line:' . $e->getLine());
        }
    }


    /**
     * 生成短信验证码
     * @param int $length
     * @return int
     */
    public function generateSMSCode($length = 4)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) -1 ;
        return rand($min, $max);
    }

}
