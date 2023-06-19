<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Captcha extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function getCode()
    {
        $config = [
            'fontSize' => 30,
            'length' => 4,
            'useNoise' => false,
            'reset'=>false
        ];
        $captcha = new \think\captcha\Captcha($config);
        return $captcha->entry();
    }

    public function checkVerify()
    {
        $code = $this->request->post('captcha');
        $captcha = new \think\captcha\Captcha();
        $res = $captcha->check($code);
        if ($res){
            $this->success('验证成功');
        }
        $this->error('验证码错误');
        
    }
}