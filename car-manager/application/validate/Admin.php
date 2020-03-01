<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Admin extends Common
{

    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('name', 'required|minlength,1', '姓名');
        $valid->addRule('mobile', 'required|mobile', '手机号');
        $valid->addRule('password', 'required|minlength,6', '密码');
        $valid->addRule('rid', 'required|int', '用户组');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
