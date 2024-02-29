<?php

namespace app\admin\command;

use app\admin\service\FeiShuService;
use app\admin\service\WxgjService;
use app\admin\service\XaqxService;
use app\admin\service\YcService;
use app\admin\service\YunsService;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class YcSendMessage extends Command
{
    protected function configure()
    {
        $site = Config::get('site');
        $this
            ->setName('YcSendMessage')
            ->addOption('check','t',Option::VALUE_OPTIONAL,'检查订单',0);
    }

    protected function execute(Input $input, Output $output)
    {
        $check = $input->getOption('check');
        switch ($check){
            case 1:
                echo "检查订单".PHP_EOL;
                $this->checkOrders();
                break;
            case 2:
                $this->getOnlineSku();
                break;
            case 3:
                YunsService::getAllOrder();
                break;
            case 4:
                XaqxService::syncStore();
                break;
            case 5:
                XaqxService::getRuleList();
                break;
            case 0:
                echo "发送消息".PHP_EOL;
                $this->send();
                break;
        }
        echo("执行完成");

    }

    public function send()
    {
//        WxgjService::checkStack();
//        WxgjService::checkUnOrders();
        $message = YcService::checkStack();
        if (!empty($message)){
            FeiShuService::sendMessage($message);
        }

    }
    public function checkOrders()
    {
        $map = [
            "23082191VENJ5U"=>"433402091422588",
            "23082194NAF6RV"=>"433402091422678",
            "230821985PTDNN"=>"433402172761115,433402091422768",
            "2308219F6EFHF1"=>"433402172761026",
            "2308219HWF6F1H"=>"433402091422948",
            "2308219M89TVNS"=>"433402091423028",
            "2308229R4ET6CC"=>"433402091423119",
            "230823C84YMUD4"=>"433405670204405",
            "230825JX17QS8A"=>"433404773703464",
            "230823DSBJ4MVR"=>"433405079245450",
            "230823E9C9504W"=>"433405661849324",
            "230823EC1R8T6B"=>"433405661849421,433405079245636",
            "230824FS04DUTK"=>"433405079245729",
        ];
        WxgjService::checkOrderNo($map);

    }

    public function getOnlineSku()
    {
//        WxgjService::getOnlineSku();
        WxgjService::getStore();

    }
}
