<?php

namespace app\service;

use app\helper\Helper;
use king\lib\Jwt;
use king\lib\Weixin;
use app\cache\Car as CarCache;
use app\model\User as UserModel;

class Login
{ 
    private static $login_url = 'https://api.weixin.qq.com/sns/jscode2session';
    private static $error_code = [
        '-1' => '系统繁忙，此时请开发者稍候再试',
        '40029' => 'code 无效',
        '45011' => '频率限制，每个用户每分钟100次'
    ];
    
    //登录
    public static function login($req)
    {
        $params = [
            'appid'  => C('app_id'),
            'secret' => C('secret'),
            'js_code' => $req['code'],
            'grant_type' => 'authorization_code',
        ];
        $res = Helper::request(self::$login_url, $params);
        CarCache::set('test', $req['code']);
        if (!isset($res['openid'])) {
            return ['code' => 400, 'data' => $res['errmsg']];
        }

        $user_info = UserModel::where(['openid' => $res['openid']])->find();
        if ($user_info) {
            $payload = [
                "user_id"     => $user_info['id'],
                "openid"      => $res['openid'],
                "session_key" => $res['session_key'],
                "time"        => time()
            ];
            $jwt = Jwt::getToken($payload);
            $data = [
                'status' => $user_info['status'],
                'token' => $jwt
            ];
        }else{
            $user_id = UserModel::insert([
                'mobile'=>$req['mobile'] ?? '',
                'nickname'=>$req['nickname'] ?? '',
                'avatar'=>$req['avatar'] ?? '',
                'realname'=>$req['realname'] ?? '',
                'level_id'=>0,
                'openid'=>$res['openid'],
                'images_url'=>'',
                'status'=>0,
                'create_time'=>time(),
                'last_login_time'=>time(),
            ]);

            $payload = [
                "user_id"     => $user_id,
                "openid"      => $res['openid'],
                "session_key" => $res['session_key'],
                "time"        => time()
            ];
            $jwt = Jwt::getToken($payload);

            $data = [
                'status' => 0,
                'token' => $jwt
            ];
        }


            // 测试登录
        // $payload = [
        //         "user_id"     => 10000,
        //         "openid"      => '123123123',
        //     ];
        //     $jwt = Jwt::getToken($payload);
        //     dd($jwt);
        //     $data = [
        //         'status' => 1,
        //         'token' => $jwt
        //     ];
        return ['code' => 200, 'data' => $data];
    }
    //便捷登录（方便测试）
    public static function easyLogin($req)
    {
        $user_info = UserModel::where(['openid' => $req['openid']])->find();
        if ($user_info) {
            $payload = [
                "user_id"     => $user_info['id'],
                "openid"      => $req['openid'],
                "session_key" => '',
                "time"        => time()
            ];
            $jwt = Jwt::getToken($payload);
            $data = [
                'status' => $user_info['status'],
                'token' => $jwt
            ];
        }else {
            return ['code' => 400, 'data' => "openid不存在"];
        }
        return ['code' => 200, 'data' => $data];
    }

}
