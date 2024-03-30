<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;

use think\Controller;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 订单跟进管理
 *
 * @icon fa fa-circle-o
 */
class Orderstrack extends Backend
{
    const UPDATE_FIELDS = [
        'orders_to_factory_date',
        'orders_from_factory_date',
        'orders_to_ck_date',
        'orders_to_yw_date',
        'orders_from_yw_date',
        'orders_to_ck_date'
    ];

    const UPDATE_FIELDS_MAP = [
        'orders_to_factory_date'=>'发给工厂时间',
        'orders_from_factory_date'=>'工厂发出时间',
        'orders_to_ck_date'=>'到达仓库时间',
        'orders_to_yw_date'=>'到达义乌时间',
        'orders_from_yw_date'=>'义乌发出时间'
    ];
    const ORDER_STATUS_TEXT = [
        'pending'=>'待出货-未申请',
        'packed'=>'待出货-已申请',
        'packed_unconfirmed'=>'已申请-未确认',
        'ready_to_ship'=>'待出货-已取件',
        'shipped'=>'运输中',
        'in_cancel'=>'取消中',
        'completed'=>'已完成',
        'canceled'=>'已取消',
        'confirmed'=>'已收货',
        'returned'=>'已退货',
        'other'=>'其他',
    ];
    /**
     * OrdersTrack模型对象
     * @var \app\admin\model\OrdersTrack
     */
    protected $model = null;
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['index','batch_update','edit'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('OrdersTrack');
        $this->assign('field_map',self::UPDATE_FIELDS_MAP);
        $this->assignconfig('order_status',self::ORDER_STATUS_TEXT);

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个方法
     * 因此在当前控制器中可不用编写增删改查的代码,如果需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order('orders_id', 'desc')
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function batch_update($ids = null)
    {

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                if (empty($params['ids'])){
                    $this->error("订单ID 不能为空");
                }
                if (empty($params['field'])){
                    $this->error('请选择更新字段');
                }
                $update = [];
                if (in_array($params['field'],self::UPDATE_FIELDS)){
                    $update[$params['field']] = $params['date'];
                }
                if ($params['factory_number']){
                    $update['factory_number'] = $params['factory_number'];
                }
                if ($params['factory_remark']){
                    $update['factory_remark'] = $params['factory_remark'];
                }
                $idsArr = explode(",",$params['ids']);
                $result = Db::table('orders_track')->whereIn('orders_id',$idsArr)->update($update);
                if ($result != false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }
    

}
