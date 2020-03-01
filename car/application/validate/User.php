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
}
