<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Shop extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('province_id', 'required|int|gt,0', '省份');
        $valid->addRule('name', 'required', '门店名称');
        $valid->addRule('address', 'required', '门店地址');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
