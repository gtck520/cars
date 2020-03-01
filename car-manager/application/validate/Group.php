<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Group extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('powerid', 'required|minlength,1', '拥有权限');
        $valid->addRule('rolename', 'required|minlength,1', '角色名');
        $res = explode('|',trim($post['powerid'],'|'));
        if (!is_array($res)) {
            Response::SendResponseJson(400, 'powerid validate error');
        }
        foreach ($res as $v) {
            if (!is_numeric($v)) {
                Response::SendResponseJson(400, 'powerid validate error');
            }
        }
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
