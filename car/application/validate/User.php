<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class User extends Common
{

    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('mobile', 'required', '手机号码');
        $valid->addRule('shop_name', 'required', '门店名称');
        $valid->addRule('realname', 'required', '真实姓名');
        $valid->addRule('city_id', 'required', '城市id');
        $valid->addRule('taocan_id', 'required', '套餐id');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

    //充值验证
    public static function rechargeValidate($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('amount', 'required|gte,0.01', '金额');
        $valid->addRule('pay_type', 'required', '支付状态');
        $valid->addRule('method', 'required', '支付方法');
        $valid->addRule('type', 'required', '充值类型');

        if (!in_array($post['type'], ['money'])) {
            Response::SendResponseJson(400, '充值类型错误');
        }
        if (!in_array($post['method'], ['miniapp'])) {
            Response::SendResponseJson(400, '支付方法错误');
        }
        if (!in_array($post['pay_type'], ['wechat'])) {
            Response::SendResponseJson(400, '支付状态错误');
        }
        
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
