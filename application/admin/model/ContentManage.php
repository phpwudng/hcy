<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class ContentManage extends Model
{

    

    

    // 表名
    protected $table = 'content_manage';
    
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

    /**
     * 获取广告位
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($id=1)
    {
        $redisKey = RedisConst::ABORTME.$id;
        $data = Redis::instance()->get($redisKey);
        if (empty($data)){
            $res = $this->where('id',$id)->order('sort','desc')->find();
            if(! empty($res)){
                $data = [
                    'id'=>$res['id'],
                    'sort'=>$res['sort'],
                    'title'=>$res['title'],
                    'image'=>$res['image'],
                    'content'=>$res['content'],
                ];

                $data = json_encode($data);
                Redis::instance()->setex($redisKey,86400,$data);
            }
        }
        $data = json_decode($data,true);
        return $data;

    }




}
