<?php

namespace app\index\controller;
use app\common\controller\BaseController;
use app\common\library\Ua;
use app\common\service\WxService;

class Index extends BaseController
{
    private $isMoble = false;
    public function _initialize()
    {
        $config['tel'] = \think\Config::get('site.tel');
        $config['topimage'] = \think\Config::get('site.topimage');
        $config['indexqq'] = \think\Config::get('site.indexqq');
        $config['address'] = \think\Config::get('site.address');
        $config['qrcode'] = \think\Config::get('site.qrcode');
        $config['icp'] = \think\Config::get('site.icp');
        $config['icp_url'] = \think\Config::get('site.icp_url');
        $config['corporate_name'] = \think\Config::get('site.corporate_name');
        $config['yqb_logo'] = \think\Config::get('site.yqb_logo');
        $config['share_img'] = \think\Config::get('site.hcy_share_img');

        if (strpos($_SERVER['HTTP_HOST'],'huichuangye') !== false){
            $config['title'] = '惠创业官网-惠创业（北京）咨询有限公司';
        }else{
            $config['title'] = '商标注册_icp备案,edi许可证注册,sp,isp许可证办理-惠创知识产权';
        }
        $this->assign('config',$config);
        $this->isMoble = Ua::isMobile();
        if ($this->isMoble){
//            print_r($config['share_img']);
            $wxConfig = WxService::instance()->getJsapiSign();
            $wxConfig['appid']=WxService::instance()->appid;
            $this->assign('wxConfig',$wxConfig);
        }

    }

    public function index()
    {
        $answer = model('app\admin\model\Answer')->getDataAll(6);
        $successCase = model('app\admin\model\Successcase')->getDataAll();
        $service =  model('app\admin\model\Service')->getDataAll();
        $adviser =  model('app\admin\model\Adviser')->getDataAll();
        $this->assign('answer',$answer);
        $this->assign('successCase',$successCase);
        $this->assign('service',$service);
        $this->assign('adviser',$adviser);
        if ($this->isMoble){
            return $this->view->fetch('index_tel');
        }
        return $this->view->fetch();
    }

    public function list()
    {
        $type = $this->request->param('type');
        $answerList = model('app\admin\model\Answer')->getDataAll();
        $this->assign('answer',$answerList);
        $this->assign('type',$type);
        if ($this->isMoble){
            return $this->view->fetch('list_tel');
        }
        return $this->view->fetch();
    }
    public function article()
    {
        $id = (int)$this->request->get('id');
        $article = model('app\admin\model\Answer')->getArticleById($id);
        $this->assign('article',$article);
        if ($this->isMoble){
            return $this->view->fetch('article_tel');
        }
        return $this->view->fetch();
    }
}
