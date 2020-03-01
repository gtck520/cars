<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class CarColour extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('name', 'required', '颜色名称');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
