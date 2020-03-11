<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Query extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('vin', 'required|minLength,1', '车架号');
        $valid->addRule('hpzl', 'required|minLength,1', '号牌种类');
        $valid->addRule('hphm', 'required|minLength,1', '号牌号码');
        $valid->addRule('fdjh', 'required|minLength,1', '发动机号');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

}
