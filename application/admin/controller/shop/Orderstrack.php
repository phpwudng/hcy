<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;

use think\Controller;
use think\Request;

/**
 * 订单跟进管理
 *
 * @icon fa fa-circle-o
 */
class Orderstrack extends Backend
{
    
    /**
     * OrdersTrack模型对象
     */
    protected $model = null;
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['index'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('OrdersTrack');

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个方法
     * 因此在当前控制器中可不用编写增删改查的代码,如果需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

}
