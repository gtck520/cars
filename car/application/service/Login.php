<?php

namespace app\service;

use app\helper\Helper;
use king\lib\Jwt;
use app\model\User as UserModel;

class Login
{ 
    private static $login_url = 'https://api.weixin.qq.com/sns/jscode2session?';
    private static $error_code = [
        '-1' => '系统繁忙，此时请开发者稍候再试',
        '40029' => 'code 无效',
        '45011' => '频率限制，每个用户每分钟100次'
    ];
    
    //登录
    public static function login($code)
    {
        // $params = [
        //     'appid'  => C('app'),
        //     'secret' => C('secret'),
        //     'js_code' => $code,
        //     'grant_type' => 'authorization_code',
        // ];
        // $res = Helper::request(self::$login_url, $params);
        // if ($res['errcode'] !== 0) {
        //     return ['code' => 400, 'data' => $res['errmsg']];
        // }

        // $user_info = UserModel::where(['openid' => $res['openid']])->find();
        // if ($user_info) {
        //     $payload = [
        //         "user_id"     => $user_info['id'],
        //         "openid"      => $res['openid'],
        //         "session_key" => $res['session_key'],
        //         "time"        => time()
        //     ];
        //     $jwt = Jwt::getToken($payload);
        //     $data = [
        //         'status' => 1,
        //         'token' => $jwt
        //     ];
        // }else{
        //     $data = [
        //         'status' => 0,
        //         'token' => ''
        //     ];
        // }





            // 测试登录
        $payload = [
                "user_id"     => 10000,
                "openid"      => '123123123',
            ];
            $jwt = Jwt::getToken($payload);
            dd($jwt);
            $data = [
                'status' => 1,
                'token' => $jwt
            ];
        return ['code' => 200, 'data' => $data];
    }

}
