<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class Hcynews extends Model
{

    

    

    // 表名
    protected $table = 'hcynews';
    
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

    public function getListByTime($page=1,$size=10,$sort='createtime')
    {
        $offset = ($page-1)*$size;
        $count = $this->where('status','上线')
            ->field('id')
            ->count();
        $data = $this->where('status','上线')
            ->field('id,title,keywords,description,num,image,from_unixtime(updatetime) updatetime')
            ->order($sort,'desc')
            ->limit($offset, $size)
            ->select();
        $res = [
            'total'=>$count??0,
            'data'=>$data,
            'page'=>$page,
            'size'=>$size
        ];
        return $res;

    }

    public function getDetailById($id)
    {
        $redisKey = RedisConst::HCYNEWSES.$id;
        $data = Redis::instance()->hgetall($redisKey);
        if (empty($data)){
            $data = $this->where('status','上线')
                ->where('id',$id)
                ->find();
            if (empty($data)){
                $data['id'] = 0;
            }else{
                $data = $data->toArray();
            }
            Redis::instance()->hMSet($redisKey,$data);
            Redis::instance()->expire($redisKey,86400);
        }
        if (empty($data['id'])){
            return [];
        }
        $data['updatetime'] = date('Y-m-d H:i:s',$data['updatetime']);
        $num = $data['num']+1;
        $this->update(['num'=>$num],['id'=>$data['id']]);
        return $data;
    }




}
