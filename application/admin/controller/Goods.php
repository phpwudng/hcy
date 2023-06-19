<?php

namespace app\admin\controller;

use app\common\constants\RedisConst;
use app\common\controller\Backend;
use app\common\library\Redis;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 游企帮审核列管理
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
{
    
    /**
     * Goods模型对象
     * @var \app\admin\model\Goods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Goods;
        $this->view->assign("campanyTypeList", $this->model->getCampanyTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("tasTypesList", $this->model->getTasTypesList());
        $this->view->assign("tasReturnsList", $this->model->getTasReturnsList());
        $this->view->assign("invoiceStatusList", $this->model->getInvoiceStatusList());
        $this->view->assign("openAccountList", $this->model->getOpenAccountList());
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        $adviser = model('Adviser')->getNames();
        $this->assign('adviser',$adviser);
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $adviserIds = explode(',',$row['adviser_id']??'');
        $row['adviser_id'] = isset($adviserIds[0]) ? array_shift($adviserIds) : 0;
        $row['other_adviser_id'] = ! empty($adviserIds) ? implode(',',$adviserIds) : 0 ;
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $otherAdviser = $this->request->post()['other_adviser_id']??[];
            if ($params) {
                $params = $this->preExcludeFields($params);
                if ($params['status'] == '审核通过' && empty($params['adviser_id'])){
                    $this->error('请选择主顾问');
                }
                if ($params['status'] == '审核未通过' && empty($params['reason'])){
                    $this->error('请填写未通过原因');
                }
                $params['adviser_id'] .= ','.implode(',',$otherAdviser);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->delCache($ids);
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->delCache($ids);
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    private function delCache($id)
    {
        $redisKey = RedisConst::CAMPANYINFO.$id;
        Redis::instance()->del($redisKey);

    }
    

}
