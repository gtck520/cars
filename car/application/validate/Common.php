<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Common
{
    protected static $error = '';

    //
    public static function checkId($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            Response::SendResponseJson(400, 'id异常');
        }
    }

    //
    public static function checkPage($req)
    {
        $valid = Valid::getClass($req);
        $valid->addRule('p', 'gt,0|isInt|lt,100000', '页数');
        $valid->addRule('c', 'gt,0|isInt|lt,500', '每页显示数量');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

    //
    public static function getError()
    {
        return self::$error;
    }
    public static function checkVin($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('vin', 'required', '车架号');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
}
