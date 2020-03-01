<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Login extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('mobile', 'required|mobile', '手机号');
        $valid->addRule('password', 'required|minlength,6', '密码');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

}
