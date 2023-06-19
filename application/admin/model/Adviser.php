<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class Adviser extends Model
{

    

    

    // 表名
    protected $table = 'adviser';
    
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
        return ['上线' => __('上线'), '下线' => __('下线')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getDataAll()
    {
        $data = $this->where('status','上线')->order('createtime','desc')->select();
        return $data;
    }
    public function getNames()
    {
        $data = $this->where('status','上线')->order('createtime','desc')->column('id,name');
        return $data;
    }

    public function getAdviserById($id)
    {
        $redisKey = RedisConst::ADVISERINFO.$id;
        $data = Redis::instance()->hGetAll($redisKey);
        if (empty($data)){
            $res = $this->where('status','上线')->where('id',$id)->find();
            if (empty($res)){
                $data['id'] = 0;
            }else{
                $data = [
                    'id'=>$res['id'],
                    'name'=>$res['name'],
                    'description'=>$res['description'],
                    'qq'=>$res['qq'],
                    'image'=>$res['image'],
                ];
            }
            Redis::instance()->hMSet($redisKey,$data);
            Redis::instance()->expire($redisKey,86400);
        }
        if (empty($data['id'])) {
            $data = [];
        }
        return $data;


    }




}
