<?php

namespace app\admin\model;

use think\Model;


class Service extends Model
{

    

    

    // 表名
    protected $table = 'service';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'publishtime_text'
    ];
    

    
    public function getTypeList()
    {
        return ['增值电信' => __('增值电信'), '认证服务' => __('认证服务'), '知识产权' => __('知识产权'), '工商服务' => __('工商服务')];
    }

    public function getStatusList()
    {
        return ['上线' => __('上线'), '下线' => __('下线'), '删除' => __('删除')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPublishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['publishtime']) ? $data['publishtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
    public function getAnswerKey($key)
    {
        $list = $this->getTitle();
        if (isset($list[$key])){
            return $list[$key];
        }
        if (isset(array_flip($list)[$key])){
            return array_flip($list)[$key];
        }
        return 'other';
    }

    public function getTitle()
    {
        return ['增值电信' => 'zengzhi', '认证服务' => 'renzheng', '知识产权' => 'zhishi', '工商服务' => 'gongshang'];
    }
    public function getDataAll()
    {
        $data = $this->where('status','上线')->where('publishtime','<=',time())->order('createtime','desc')->select();
        $res = [];
        if (! empty($data)){

            foreach ($data as $key =>$value){
                $k = $this->getAnswerKey($value['type']);
                $res['contentList'][$k][] = $value;
            }
            $res['titleList'] = array_unique(array_keys($res['contentList']));
        }
        return $res;
    }


}
