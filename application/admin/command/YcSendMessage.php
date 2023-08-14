<?php

namespace app\admin\command;

use app\admin\service\FeiShuService;
use app\admin\service\WxgjService;
use app\admin\service\YcService;
use app\common\service\LogService;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class YcSendMessage extends Command
{
    protected function configure()
    {
        $site = Config::get('site');
        $this
            ->setName('YcSendMessage');
    }

    protected function execute(Input $input, Output $output)
    {

        $message = YcService::checkStack();
        if (!empty($message)){
            FeiShuService::sendMessage($message);
        }
//        YcService::testSign();
        WxgjService::checkUnOrders();

        LogService::info("发送完成");

    }
}
