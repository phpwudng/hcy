<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Redis;
use think\Cookie;
use think\Log;

/**
 * 首页接口
 */
class Campany extends Api
{
    protected $noNeedLogin = ['recommend', 'tagsList', 'getCampanyList', 'getCampanyById'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 推荐公司
     */
    public function recommend()
    {
        $recommend = model('app\admin\model\RecommendGoods')->getListLimit();
        $this->success('成功',$recommend);

    }

    /**
     * 筛选项
     */
    public function tagsList()
    {
        $recommend = model('app\admin\model\TagsList')->getDataAll();
        $this->success('成功',$recommend);
    }

    /**
     * 搜索接口
     */
    public function getCampanyList()
    {
        $params = $this->request->param();
        Log::info('搜索公司条件'.json_encode($params));
        $data = model('app\admin\model\Goods')->getDataAll($params);
        $this->success('成功',$data);

    }

    /**
     * 搜索公司详情接口
     */
    public function getCampanyById()
    {
        $campanyId = (int)$this->request->post('campanyId');
        if (empty($campanyId)){
            $this->error('参数错误');
        }
        $data = model('app\admin\model\Goods')->getCampanyById($campanyId);
        $this->success('成功',$data);
    }

    public function add()
    {
        $params = $this->request->post();
        if (empty($params['campanyName'])){
            $this->error('公司必须填写');
        }
        if (empty($params['industryType'])){
            $this->error('行业类型必须填写');
        }
        if (empty($params['city'])){
            $this->error('城市必须填写');
        }
        if (empty($params['campanyType'])){
            $this->error('企业类型必须填写');
        }
        if (empty($params['createDate'])){
            $this->error('公司成立日期必须填写');
        }
//        if (empty($params['createMoney'])){
//            $this->error('公司注册资本必须填写');
//        }
//        if (empty($params['currentMoney'])){
//            $this->error('实际资本必须填写');
//        }
        if (empty($params['businessScope'])){
            $this->error('公司经营范围必须填写');
        }
        if (empty($params['image'])){
            $this->error('公司营业执照必须填写');
        }
        if (empty($params['tasType'])){
            $this->error('纳税类型必须填写');
        }
        if (empty($params['tasReturns'])){
            $this->error('报税情况必须填写');
        }
        if (empty($params['invoiceStatus'])){
            $this->error('发票类型必须填写');
        }
        if (empty($params['openAccount'])){
            $this->error('银行账户必须填写');
        }
//        if (empty($params['sellMoney'])){
//            $this->error('转让金额必须填写');
//        }
        if (empty($params['contacts'])){
            $this->error('联系人必须填写');
        }
        if (empty($params['contactsTel'])){
            $this->error('转让金额必须填写');
        }
        $userId = Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        if ( empty($params['id']) && model('app\admin\model\Goods')->checkCampanyByName($params['campanyName'])){
            $this->error('该公司已经提交，请勿重复操作');
        }
        $data = model('app\admin\model\Goods')->addCampany($params,$userId);
        $this->success('成功',$data);

    }


}
