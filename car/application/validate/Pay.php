<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Pay extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('pay_trade_no', 'required|minLength,1', '第三方交易号码');
        $valid->addRule('amount', 'required|minLength,1', '金额');
        $valid->addRule('trade_no', 'required|minLength,1', '订单');
        $valid->addRule('status', 'required|int', '状态');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

}
