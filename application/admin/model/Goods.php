<?php

namespace app\admin\model;

use app\common\constants\RedisConst;
use app\common\library\Redis;
use app\common\service\LogService;
use think\Model;


/**
 * Class Goods
 * @package app\admin\model
 */
class Goods extends Model
{

    

    

    // 表名
    protected $table = 'goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'campany_type_text',
        'status_text',
        'tas_types_text',
        'tas_returns_text',
        'invoice_status_text',
        'open_account_text'
    ];
    

    
    public function getCampanyTypeList()
    {
        return ['有限责任公司' => __('有限责任公司'), '独资公司' => __('独资公司')];
    }

    public function getStatusList()
    {
        return ['待审核' => __('待审核'), '审核通过' => __('审核通过'), '审核未通过' => __('审核未通过'), '已下线' => __('已下线')];
    }

    public function getTasTypesList()
    {
        return ['小规模' => __('小规模'), '一般纳税' => __('一般纳税'), '未纳税' => __('未纳税')];
    }

    public function getTasReturnsList()
    {
        return ['正常' => __('正常'), '非正常' => __('非正常')];
    }

    public function getInvoiceStatusList()
    {
        return ['已领过发票' => __('已领过发票'), '未领过发票' => __('未领过发票')];
    }

    public function getOpenAccountList()
    {
        return ['已开户' => __('已开户'), '未开户' => __('未开户')];
    }
    public function getTasTypesKey($value)
    {
        return ['小规模' => 1, '一般纳税' => 2, '未纳税' => 3][$value] ?? 1;
    }
    public function getTasReturnsKey($value)
    {
        return ['正常' => 1, '非正常' => 2][$value] ?? 1;
    }

    public function getInvoiceStatusKey($value)
    {
        return ['已领过发票' => 1, '未领过发票' => 2][$value] ?? 1;
    }

    public function getOpenAccountKey($value)
    {
        return ['已开户' => 1, '未开户' => 2][$value] ?? 1;
    }

    public function getCampanyTypeKey($value)
    {
        return ['有限责任公司' => 1, '独资公司' => 2][$value] ?? 1;
    }

    /**
     * 将 附加资产的value 转成key
     * @param $value
     * @return string
     */
    public function getPropertyKey($value)
    {
        $tagsList = model('app\admin\model\TagsList')->getDataAll();
        $keys = [];
        if (! empty($tagsList['property'])){
            $values = explode(',',$value);
            foreach ($tagsList['property'] as $k =>$v){
                if (in_array($v,$values)){
                    $keys[] = $k;
                }
            }
        }
        return implode(',',array_unique($keys));

    }


    public function getCampanyTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['campany_type']) ? $data['campany_type'] : '');
        $list = $this->getCampanyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTasTypesTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tas_types']) ? $data['tas_types'] : '');
        $list = $this->getTasTypesList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTasReturnsTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tas_returns']) ? $data['tas_returns'] : '');
        $list = $this->getTasReturnsList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getInvoiceStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['invoice_status']) ? $data['invoice_status'] : '');
        $list = $this->getInvoiceStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getOpenAccountTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['open_account']) ? $data['open_account'] : '');
        $list = $this->getOpenAccountList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getDataAll($params=[])
    {
        $tagsList = model('app\admin\model\TagsList')->getDataAll();
        $page = !empty($params['page'])? (int)$params['page'] : 1;
        $size = !empty($params['size'])? (int)$params['size'] : 10;
        $page = ($page-1)*$size;
        $where = '';
        //城市
        if (!empty($params['city'])){
            $city = (int)$params['city'];
            $where = ' and city='.$city;
        }
        //公司类型
        if (!empty($params['campnayType'])){
            $campnayType = (int)$params['campnayType'];
            $where .= ' and campany_type='.$campnayType;
        }
        //行业类型
        if (!empty($params['industryType']) && !empty($tagsList['industry_type']) && isset($tagsList['industry_type'][$params['industryType']]) ){
            $industryType = $tagsList['industry_type'][$params['industryType']];
            $where .= " and industry_type='{$industryType}'";
        }
        //纳税类型
        if (!empty($params['tasTypes'])){
            $tasTypes = (int)$params['tasTypes'];
            $where .= ' and tas_types='.$tasTypes;
        }
        //是否开户
        if (!empty($params['openAccount'])){
            $openAccount = (int)$params['openAccount'];
            $where .= ' and open_account='.$openAccount;
        }
        //附带资产
        $orwhere = '';
        if (!empty($params['property'])){
            $property = explode(',',$params['property']);
            foreach ($property as $v){
                if ($v == -1){
                    $orwhere .= " FIND_IN_SET('其他',property) or";
                    continue;
                }
                if (isset($tagsList['property'][$v])){
                    $orwhere .= " FIND_IN_SET('{$tagsList['property'][$v]}',property) or";
                }
            }
            $orwhere = trim($orwhere,'or');
            if (!empty($orwhere)){
                $orwhere = " and ($orwhere)";
            }
        }
        //注册资本
        if (!empty($params['createMoney'])){
            switch ($params['createMoney']){
                case 1:
                    $where .= ' and create_money<50';
                    break;
                case 2:
                    $where .= ' and create_money>=50 and create_money<100';
                    break;
                case 3:
                    $where .= ' and create_money>=100 and create_money<500';
                    break;
                case 4:
                    $where .= ' and create_money>=500 and create_money<1000';
                    break;
                case 5:
                    $where .= ' and create_money>=1000';
                    break;
            }
        }
        //出售价格
        if (!empty($params['sellMoney'])){
            switch ($params['sellMoney']){
                case 1:
                    $where .= ' and sell_money<1';
                    break;
                case 2:
                    $where .= ' and sell_money>=1 and sell_money<3';
                    break;
                case 3:
                    $where .= ' and sell_money>=3 and sell_money<5';
                    break;
                case 4:
                    $where .= ' and sell_money>=5 and sell_money<10';
                    break;
                case 5:
                    $where .= ' and sell_money>=10 and sell_money<30';
                    break;
                case 6:
                    $where .= ' and sell_money>=30 and sell_money<100';
                    break;
                case 7:
                    $where .= ' and sell_money>=100';
                    break;
            }
        }
        //注册时间
        if (!empty($params['createDate'])){
            switch ($params['createDate']){
                case 1:
                    $date = date('Ymd',time()-86400*365);
                    $where .= ' and create_date>'.$date;
                    break;
                case 2:
                    $beginDate = date('Ymd',time()-86400*365*2);
                    $endDate = date('Ymd',time()-86400*365);
                    $where .= " and create_date>{$beginDate} and create_date<={$endDate}";
                    break;
                case 3:
                    $beginDate = date('Ymd',time()-86400*365*3);
                    $endDate = date('Ymd',time()-86400*365*2);
                    $where .= " and create_date>{$beginDate} and create_date<={$endDate}";
                    break;
                case 4:
                    $date = date('Ymd',time()-86400*365*3);
                    $where .= ' and create_date<'.$date;
                    break;
            }
        }

        $sql = "select id,campany_name_show,industry_type,campany_type,tas_types,create_money,create_date,open_account,sell_money,from_unixtime(updatetime) updatetime,adviser_id,'928553' as qq from goods where status=2 {$where} {$orwhere} order by createtime desc limit {$page}, {$size}";
        $sqlCont = "select count(1) num from goods where status=2 {$where} {$orwhere}";
        $count = $this->query($sqlCont);
        $data = $this->query($sql);
        if (! empty($data)){
            foreach ($data as $k=>&$v){
                $adviserids = explode(',',$v['adviser_id']);
                $createDate = $v['create_date'];
                $v['create_date'] = $this->formateYear($createDate);
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
            'total'=>$count[0]['num']??0,
            'data'=>$data,
            'page'=>$page,
            'size'=>$size
        ];
        return $res;
    }

    /**
     * 获取公司详情
     * @param $id
     * @return array
     */
    public function getCampanyById($id)
    {
        $redisKey = RedisConst::CAMPANYINFO.$id;
        $data = Redis::instance()->hGetAll($redisKey);
//        $tagsList = model('app\admin\model\TagsList')->getDataAll();
        if (empty($data)){
            $res = $this->where('status','审核通过')->where('id',$id)->find();
            if (empty($res)){
                $data['id']=0;
            }else{
                $data =[
                    'id'=>$res['id'],
                    'campany_name'=>$res['campany_name_show'],
                    'industry_type'=>$res['industry_type'],
                    'campany_type'=>$res['campany_type'],
                    'property'=>$res['property'],
                    'create_money'=>$res['create_money'],
                    'create_date'=>$res['create_date'],
                    'city'=>$res['city'],
                    'sell_money'=>$res['sell_money'],
                    'business_scope'=>$res['business_scope'],
                    'tas_types'=>$res['tas_types'],
                    'tas_returns'=>$res['tas_returns'],
                    'invoice_status'=>$res['invoice_status'],
                    'open_account'=>$res['open_account'],
                    'adviser_id'=>$res['adviser_id'],
                    'current_money'=>$res['current_money'],
                    'updatetime'=>$res['updatetime'],
                    'createtime'=>$res['createtime'],

                ];

            }
            Redis::instance()->hMSet($redisKey,$data);
            Redis::instance()->expire($redisKey,86400);
        }
        if (empty($data['id'])){
            $data = [];
        }else{
            //处理经营时间
            $data['create_year'] = $this->formateYear($data['create_date']);
            $data['web'] = strpos($data['property'],'网站网店') === false ? '无' : '有';;
            //商标专利
            $data['trademark'] = strpos($data['property'],'商标专利') === false ? '无' : '有';
            $data['entitled'] = strpos($data['property'],'资质批文') === false ? '无' : '有';
            $data['channel_resource'] = strpos($data['property'],'渠道资源') === false ? '无' : '有';;
            $data['has_trademark'] = strpos($data['property'],'商标专利') === false ? '否' : '是';;
            $data['has_car'] = strpos($data['property'],'车牌') === false ? '无' : '是';
            $data['has_entitled'] = strpos($data['property'],'资质批文') === false ? '否' : '是';;
            $data['has_register'] = '否';
            //处理顾问
            $adviserids = explode(',',$data['adviser_id']);
            $data['advisers'] = [];
            foreach ($adviserids as $k=>$v){
                if(empty($v)){
                    continue;
                }
                $id = intval($v);
                $data['advisers'][] = model('app\admin\model\Adviser')->getAdviserById($id);
            }
        }
        return $data;
    }

    /**
     * 检查公司是否已经注册
     * @param $campanyName
     * @return bool
     */
    public function checkCampanyByName($campanyName)
    {
//        $redisKey = RedisConst::CHECKCAMPANY.$campanyName;
//        $data = Redis::instance()->get($redisKey);
        //if (empty($data)){
            $res = $this->where('campany_name',$campanyName)->find();
            $data = empty($res) ? -1 : $res['id'];
//            Redis::instance()->setex($redisKey,86400,$data);
        //}
        $res = $data == -1 ? false : true;

        return $res;
    }

    /**
     * 检查公司是否已经注册
     * @param $campanyId
     * @return bool
     */
    public function checkCampanyById($campanyId)
    {
        $redisKey = RedisConst::CAMPANYINFO.$campanyId;
        $data = Redis::instance()->get($redisKey);
        if (empty($data)){
            $res = $this->where('id',$campanyId)->find();
            $data = empty($res) ? -1 : $res['id'];
            Redis::instance()->setex($redisKey,86400,$data);
        }
        $res = $data == -1 ? false : true;

        return $res;
    }

    /**
     * 我发表的公司
     * @param $userId
     * @param int $status
     * @param $page
     * @param $size
     * @return mixed
     */
    public function myCampanyList($userId,$status=0,$page=1,$size=10)
    {
        $where = 'where user_id ='.$userId;
        if($status){
            $where .= ' and status='.(int)$status;
        }
        $data =[];
        $page = ($page-1)*$size;
        $size += $page;
        $sqlCount = "select count(1) num from goods {$where}";
        $sql = "select * from goods {$where} limit {$page},{$size}";
        $count = $this->query($sqlCount);
        $count = isset($count[0])?$count[0]['num']:0;
        if (! empty($count)){
            $data = $this->query($sql);
        }

        return [$count,$data];
    }

    /**
     * @param $userId
     * @param $id
     * @return array
     */
    public function getMyCampanyInfoById($userId,$id)
    {
        $redisKey = RedisConst::CAMPANYINFO.$id;
        $data = Redis::instance()->hGetAll($redisKey);
        if (empty($data)){
            $res = $this->where('user_id',$userId)->where('id',$id)->find();
            if (empty($res)){
                $data['id']=0;
            }else{
                $data = $res->toArray();

            }
            Redis::instance()->hMSet($redisKey,$data);
            Redis::instance()->expire($redisKey,86400);
        }
        if (empty($data['id'])){
            $data = [];
        }else{
            $data['tas_types'] = $this->getTasTypesKey($data['tas_types']);
            $data['tas_returns'] = $this->getTasReturnsKey($data['tas_returns']);
            $data['invoice_status'] = $this->getInvoiceStatusKey($data['invoice_status']);
            $data['open_account'] = $this->getOpenAccountKey($data['open_account']);
            $data['campany_type'] = $this->getCampanyTypeKey($data['campany_type']);
            $data['property_text'] = $data['property'];
            $data['property'] = $this->getPropertyKey($data['property']);
        }
        return $data;
    }

    /**
     * 添加接口
     */
    public function addCampany($params,$userId)
    {
        $tagsList = model('app\admin\model\TagsList')->getDataAll();
        $property = '';
        //处理其他资产字段
        if (!empty($params['property'])){
            $propertys = explode(',',$params['property']);
            foreach ($propertys as $k=>$v){
                if(isset($tagsList['property'][$v])){
                    $property .= $tagsList['property'][$v];
                    $property .= ',';
                }
            }
        }

        $insert = [
            'campany_name' => (string)$params['campanyName'],
            'campany_name_show'=>(string)$params['campanyName'],
            'industry_type' => $tagsList['industry_type'][$params['industryType']] ?? '其他',
            'city' => $tagsList['city'][$params['city']] ?? '其他',
            'campany_type' => (int)$params['campanyType'],
            'create_date' => (string)$params['createDate'],
            'create_money' => (float)$params['createMoney'],
            'current_money' => (float)$params['currentMoney'],
            'business_scope' => (string)$params['businessScope'],
            'image' => (string)$params['image'],
            'tas_types' => (int)$params['tasType'],
            'tas_returns' => (int)$params['tasReturns'],
            'invoice_status' => (string)$params['invoiceStatus'],
            'open_account' => (string)$params['openAccount'],
            'sell_money' => (string)$params['sellMoney'],
            'contacts' => (string)$params['contacts'],
            'contacts_tel' => (string)$params['contactsTel'],
            'property'=>$property,
            'user_id'=>$userId,
            'updatetime'=>time(),
        ];
        $id = $params['id']??0;
        if(! empty($id) && $this->where(['id'=>$id,'user_id'=>$userId])->find()){
            $res = $this->update($insert,['id'=>(int)$id]);
        }else{
            $insert['createtime'] = time();
            $res = $this->insert($insert);
            $id = $res;
        }
        $this->delCampanyCache($id);
        $this->delCampanyCache($insert['campany_name']);
        return $res;
    }

    /**
     * 公司下架
     * @param $userId
     * @param $campanyId
     * @return bool
     */
    public function offCampany($userId,$campanyId)
    {
        if (! $this->where(['id'=>$campanyId,'user_id'=>$userId])->find()){
            return false;
        }
        $this->update(['status'=>'已下线'],['id'=>$campanyId]);
        $this->delCampanyCache($campanyId);
        return true;

    }

    public function formateYear($createDate)
    {
        $createYear = date('Y',strtotime($createDate));
        $currentYear = date('Y');
        $year = intval($currentYear-$createYear);
        switch ($year){
            case 0:
                $year = 1;
                break;
            case 1:
                $year = 2;
                break;
            case 2:
            case 3:
                $year = 3;
                break;
            default:
                $year = 4;
                break;
        }
        $text = model('app\admin\model\TagsList')->createYearContent();
        $yearText = $text[$year]??'一年以内';

        return $yearText;
    }

    public function delCampanyCache($string)
    {
        $redisKey = RedisConst::CAMPANYINFO.$string;
        Redis::instance()->del($redisKey);
    }



}
