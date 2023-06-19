<?php

namespace app\admin\model;

use think\Exception;
use think\Model;


class UserSubscribe extends Model
{

    

    

    // 表名
    protected $table = 'user_subscribe';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function follow($campanyId,$userId)
    {
        $insert=[
            'user_id'=>$userId,
            'goods_id'=>$campanyId,
            'createtime'=>time(),
            'updatetime'=>time(),
        ];
        try {
            $res = $this->insert($insert);
        }catch (Exception $exception){
            $res = 0;
        }

        return (int)$res;

    }

    public function unfollow($campanyId,$userId)
    {

        $res = $this->where('user_id',$userId)->where('goods_id',$campanyId)->delete();
        return (int)$res;

    }

    public function subscribeCampany($userId,$page,$size)
    {
        $offset = ($page-1)*$size;
        $sql = "select g.id,campany_name_show,industry_type,campany_type,tas_types,create_money,create_date,open_account,sell_money,from_unixtime(g.updatetime) updatetime,adviser_id,'928553' as qq from user_subscribe as u join goods as g on g.id=u.goods_id where u.user_id={$userId} and g.status=2 order by u.createtime desc limit {$offset},{$size}";
        $sqlCount = "select count(1) num from user_subscribe as u join goods as g on g.id=u.goods_id where u.user_id={$userId} and g.status=2 ";
        $count = $this->query($sqlCount);
        $data = $this->query($sql);
        if (! empty($data)){
            foreach ($data as $k=>&$v){
                $adviserids = explode(',',$v['adviser_id']);
                $v['create_date'] = model('app\admin\model\Goods')->formateYear($v['create_date']);
                foreach ($adviserids as $key=>$value){
                    if(empty($value)){
                        continue;
                    }
                    $id = intval($value);
                    $adviserInfo = model('app\admin\model\Adviser')->getAdviserById($id);
                    $v['qq'] = $adviserInfo['qq'];
                    break;
                }
            }
        }
        $res = [
            'page'=>$page,
            'size'=>$size,
            'total'=>$count[0]['num']??0,
            'data'=>$data
        ];
        return $res;

    }
    

    







}
