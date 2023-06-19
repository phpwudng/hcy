<?php

namespace app\api\controller;

use app\admin\model\Hcynews;
use app\common\controller\Api;
use app\common\library\Redis;

/**
 * 首页接口
 */
class Hcynewses extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function list()
    {
        $page = $this->request->post('page',1);
        $limit = $this->request->post('size',10);
        $data = model('app\admin\model\Hcynews')->getListByTime($page,$limit);
        $this->success('请求成功',$data);
    }

    /**
     * 热点资讯
     */
    public function hotList()
    {
        $data = model('app\admin\model\Hcynews')->getListByTime(1,10,'num');
        $this->success('请求成功',$data);
    }

    /**
     * 详情页面
     */
    public function detail()
    {
        $id = (int)$this->request->post('id',1);
        $data = model('app\admin\model\Hcynews')->getDetailById($id);
        $this->success('请求成功',$data);
    }
}