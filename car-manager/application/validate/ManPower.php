<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class ManPower extends Common
{

    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('controller', 'required|minlength,1', '控制器类名');
        $valid->addRule('action', 'required|minlength,1', '方法名');
        $valid->addRule('powername', 'required|minlength,1', '权限名称');
        $valid->addRule('sort', 'required|int|gt,0', '排序值');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
