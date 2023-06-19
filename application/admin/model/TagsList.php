<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use think\Model;


class TagsList extends Model
{

    

    

    // 表名
    protected $table = 'tags_list';
    
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
        return ['附带资产' => __('附带资产'), '行业类型' => __('行业类型'), '所属城市' => __('所属城市')];
    }

    public function getStatusList()
    {
        return ['上线' => __('上线'), '下线' => __('下线')];
    }

    public function campanyTypeContent()
    {
        return ['1'=>'有限责任公司','2'=>'独资公司'];
    }
    public function tasTypesContent()
    {
        return ['1'=>'小规模','2'=>'一般纳税','3'=>'未纳税'];
    }
    public function openAccountContent()
    {
        return ['1'=>'已开基本户','2'=>'未开基本户'];
    }
    public function createMoneyContent()
    {
        return ['1'=>'50万以下','2'=>'50-100万','3'=>'100-500万','4'=>'500-1000万','5'=>'1000万以上'];
    }
    public function createYearContent()
    {
        return ['1'=>'1年以内','2'=>'1-2年','3'=>'2-3年','4'=>'3年以上'];
    }
    public function sellMoneyContent()
    {
        return ['1'=>'1万以下','2'=>'1-3万','3'=>'3-5万','4'=>'5-10万','5'=>'10-30万','6'=>'30-100万','7'=>'100万以上'];
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
    public function getTagsKey($key)
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
        return ['附带资产' => 'property', '行业类型' => 'industry_type', '所属城市' => 'city'];
    }

    public function getDataAll()
    {
        $redisKey = RedisConst::TAGSLIST;
        $data = Redis::instance()->get($redisKey);
        if (empty($data)) {
            $tags = $this->where('status', '上线')->order('id', 'asc')->column('id,type,title');
            $res = [];
            if (!empty($tags)) {
                foreach ($tags as $key => $value) {
                    $k = $this->getTagsKey($value['type']);
                    $res[$k][$key] = $value['title'];
                }
                $data = json_encode($res);
                Redis::instance()->setex($redisKey,86400,$data);
            }
        }
        $data = json_decode($data,true);
        if (!empty($data['property'])){
            $data['property']['-1'] = '其他';
        }
        if (!empty($data['industry_type'])){
            $data['industry_type']['-1'] = '其他';
        }
        if (!empty($data['city'])){
            $data['city']['-1'] = '其他';
        }
        $data['sell_money'] = $this->sellMoneyContent();
        $data['create_year'] = $this->createYearContent();
        $data['create_money'] = $this->createMoneyContent();
        $data['open_account'] = $this->openAccountContent();
        $data['campany_type'] = $this->campanyTypeContent();
        $data['tas_type'] = $this->tasTypesContent();

        return $data;
    }




}
