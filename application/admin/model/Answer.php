<?php

namespace app\admin\model;

use think\Model;


class Answer extends Model
{

    

    

    // 表名
    protected $table = 'answer';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];
    

    
    public function getTypeList()
    {
        return ['公司并购疑难' => __('公司并购疑难'), '商标注册疑难' => __('商标注册疑难'), '增值电信许可证疑难' => __('增值电信许可证疑难'), '知识产权疑难' => __('知识产权疑难')];
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
        return ['公司并购疑难' => 'gongsi_answer', '商标注册疑难' => 'shangbiao_answer', '增值电信许可证疑难' => 'zengzhi_answer', '知识产权疑难' => 'zhishi_answer'];
    }
    public function getStatusList()
    {
        return ['上线' => __('上线'), '下线' => __('下线')];
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

    public function getDataAll($limit=0)
    {
        $data = $this->where('status','上线')->order('updatetime','desc')->column('id,type,name,updatetime,description,image,from_unixtime(createtime) createtime');
        $res = [
            'titleList'=>[],
            'contentList'=>[],
        ];
        if (! empty($data)){

            foreach ($data as $key =>$value){
                $k = $this->getAnswerKey($value['type']);
                if (!empty($limit) && isset($res['contentList'][$k]) && count($res['contentList'][$k]) == $limit){
                    continue;
                }
                $res['contentList'][$k][] = $value;
            }
            $res['titleList'] = array_unique(array_keys($res['contentList']));
        }
        return $res;
    }

    public function getArticleById($id)
    {
        $content = $this->where('status','上线')->where('id',$id)->find();
        return $content;

    }




}
