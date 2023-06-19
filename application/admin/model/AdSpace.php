<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class AdSpace extends Model
{

    

    

    // 表名
    protected $table = 'ad_space';
    
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

    /**
     * 获取广告位
     * @return mixed
     */
    public function getList()
    {
        $redisKey = RedisConst::ADSPACE;
        $data = Redis::instance()->get($redisKey);
        if (empty($data)){
            $res = $this->where('status','上线')->order('sort','desc')->select();
            if(! empty($res)){
                $data = [];
                foreach ($res as $key => $value){
                    $temp = [
                        'id'=>$value['id'],
                        'sort'=>$value['sort'],
                        'title'=>$value['title'],
                        'image'=>$value['image'],
                        'url'=>$value['url'],
                    ];
                    $data[] = $temp;
                }
                $data = json_encode($data);
                Redis::instance()->setex($redisKey,86400,$data);
            }
        }
        $data = json_decode($data,true);
        return $data;

    }




}
