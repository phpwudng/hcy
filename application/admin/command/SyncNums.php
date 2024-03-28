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
        $data = XaqxService::getAllOrders();
    }
}
