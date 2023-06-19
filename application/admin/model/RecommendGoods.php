<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class RecommendGoods extends Model
{

    

    

    // 表名
    protected $table = 'recommend_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['上线' => __('上线'), '下线' => __('下线'), '删除' => __('删除')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getListLimit()
    {
        $redisKey = RedisConst::RECOMENDGOODS;
        $data = Redis::instance()->get($redisKey);
        if (empty($data)){
            $recommend = $this->where('status','上线')->order('sort','desc')->column('goods_id');
            if(! empty($recommend)){
                $ids = array_values($recommend);
                $data = json_encode($ids);
                Redis::instance()->setex($redisKey,86400,$data);
            }
        }
        $ids = json_decode($data,true);
        $data = [];
        //获取公司详细信息
        if (! empty($ids)){
            foreach ($ids as $id){
                $tmp = model('app\admin\model\Goods')->getCampanyById($id);
                if (! empty($tmp) && count($data)<4){
                    $data[] = $tmp;
                }
            }
        }

        return $data;
    }




}
