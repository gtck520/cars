<?php

namespace app\controller\www;

use king\lib\Response;
use app\cache\Token;
use app\service\Login as LoginService;
use app\validate\Login as LoginValidate;

class Login
{
    //登录
    public function login(){
        $req = P();
        LoginValidate::checkInput($req);
        $res = LoginService::login($req['mobile'], $req['password']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //验证token
    public function checkToken(){
        $token = H('token');
        if (!$token || empty($token)) {
            Response::SendResponseJson(400, '验证失败');
        };
        //本地缓存中获取
        $admin_id = Token::get($token);
        if (!is_numeric($admin_id)) {
            Response::SendResponseJson(400, '验证失败');
        }
            
        Response::SendResponseJson(200, '');
    }
    
}
