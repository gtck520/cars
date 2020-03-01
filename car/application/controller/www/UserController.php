<?php

namespace app\controller\www;

use king\lib\Request;
use king\lib\Response;
use king\lib\Jwt;
use king\core\Route;

class UserController
{
    protected static $user_id;
    protected static $user_info;

    public function __construct()
    {
        if (!self::loginId()) {
            Response::SendResponseJson(401, '未登录');
        }
    }

    public static function loginId()
    {
        $token = Request::header('token');
        if (!$token || empty($token)) return false;
        $user_info = Jwt::checkToken($token);
        if ($user_info) {
            self::$user_id = $user_info['user_id'];
            self::$user_info = $user_info;
            return true;
        }
        return false;
    }

}
