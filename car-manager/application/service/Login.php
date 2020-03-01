<?php

namespace app\service;

use app\cache\Token;
use app\helper\Helper;
use app\model\Admin as AdminModel;

class Login
{
    //登录
    public static function login($mobile, $password)
    {
        $res = AdminModel::where(['mobile' => $mobile, 'password' => Helper::getPassword($password)])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '用户名或密码错误'];
        }
        $admin_id = $res['id'];
        $token = Token::set($admin_id, Token::$expire);
        return ['code' => 200, 'data' => ['token' => $token]];
    }

}
