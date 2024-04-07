<?php

namespace app\admin\command;

use app\admin\service\FeiShuService;
use app\admin\service\WxgjService;
use app\admin\service\XaqxService;
use app\admin\service\YcService;
use app\admin\service\YunsService;
use app\common\library\Redis;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;

class SyncNums extends Command
{
    protected function configure()
    {
        $this
            ->setName('SyncNums')
            ->addOption('type','t',Option::VALUE_OPTIONAL,'类型',0);
    }

    protected function execute(Input $input, Output $output)
    {
        $type = $input->getOption('type');
        switch ($type){
            case 'create_rule':
                echo "创建同步库存规则".PHP_EOL;
                $this->getSku();
                break;
            case 'all_orders':
                $this->getAllOrders();
                break;
            case 'store_num':
                $this->sendStore();
                break;

        }
        echo("执行完成");

    }

    private function getSku()
    {
        //获取有存储的sku
        try {
            $storeSkus = XaqxService::getStoreSku();
            XaqxService::setRuleByLocalProduct($storeSkus);

        }catch (\Throwable $throwable){
            echo $throwable->getFile();
            echo PHP_EOL;
            echo $throwable->getLine();
            echo PHP_EOL;
            echo $throwable->getMessage();
            echo PHP_EOL;
        }
    }

    private function getAllOrders()
    {
        $url = "/agent-foreign/order/list?_t=1712460524&dgStatus=2&queryType=1&orderBy=0&column=createTime&order=desc&field=id,";
        $data = XaqxService::getAllOrders($url);
        $url = "/agent-foreign/order/list?_t=1698238191&agoFlag=0&orderBy=2&column=createTime&order=desc&field=id,&dgStatus=allhandle";
        $data = XaqxService::getAllOrders($url);

    }

    private function sendStore()
    {
        $arr = [
            "dd_gzt_cf_yq_45_70",
            "dd_gzt_cf_yq_45_150",
            "dd_gzt_cf_yq_45_200",
            "dd_gzt_cf_yq_50_220",
            "dd_gzt_cf_blsb_45_70",
            "dd_gzt_cf_qsdls_d_45_70",
            "dd_gzt_ys_bsdlby_40_60",
            "dd_gzt_yanq_by_40_60",
            "dd_gzt_ys_klb_by_40_60",
            "dd_gzt_ys_yssh_40_60",
            "dd_gzt_ys_mmbb_45_70",
            "dd_gzt_ys_cxmm_45_70",
            "dd_gzt_ys_cfmm_45_70",
            "dd_gzt_ys_hfzsnb_45_70",
            "dd_gzt_ys_hssnb_45_70",
            "dd_gzt_ys_lxsnb_45_70",
            "dd_gzt_ys_hpym_45_70",
            "dd_gzt_ys_dlsmnh_45_70",
            "dd_gzt_ys_kmlbxh_45_70",
            "dd_gzt_ys_yyysh_45_70",
            "dd_gzt_ys_kkm_45_70",
            "dd_gzt_ys_zmxcy_45_70",
        ];
        XaqxService::checkStack($arr);

    }
}
