<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Redis;
use app\common\service\WxService;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }


    public function config()
    {
        $config['banners'] = model('app\admin\model\Banners')->getList();
        $config['adspace'] = model('app\admin\model\AdSpace')->getList();
        $config['config']['tel'] = \think\Config::get('site.tel');
        $config['config']['topimage'] = \think\Config::get('site.topimage');
        $config['config']['indexqq'] = \think\Config::get('site.indexqq');
        $config['config']['address'] = \think\Config::get('site.address');
        $config['config']['qrcode'] = \think\Config::get('site.qrcode');
        $config['config']['icp'] = \think\Config::get('site.icp');
        $config['config']['icp_url'] = \think\Config::get('site.icp_url');
        $config['config']['corporate_name'] = \think\Config::get('site.corporate_name');
        $config['wx_share'] = WxService::instance()->getYqbJsapiSign();
        $this->success('成功',$config);

    }

    public function abortMe()
    {
        $data = model('app\admin\model\ContentManage')->getList(1);
        $data['tel'] = \think\Config::get('site.tel');
        $data['topimage'] = \think\Config::get('site.topimage');
        $data['indexqq'] = \think\Config::get('site.indexqq');
        $data['address'] = \think\Config::get('site.address');
        $data['qrcode'] = \think\Config::get('site.qrcode');
        $data['icp'] = \think\Config::get('site.icp');
        $data['email'] = \think\Config::get('site.email');
        $data['zcode'] = 100000;
        $data['corporate_name'] = \think\Config::get('site.corporate_name');
        $this->success('成功',$data);
    }

    public function agree()
    {
        $data = model('app\admin\model\ContentManage')->getList(3);
        $data = $data['content']??'';
        $this->success('成功',$data);
    }
}
