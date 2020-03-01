<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Login as LoginService;
use app\validate\Login as LoginValidate;
use app\service\User as UserService;
use app\validate\User as UserValidate;

class Login
{
    //登录
    public function login(){
        $req = P();
        LoginValidate::checkInput($req);
        $res = LoginService::login($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
    public function register(){
        $req = P();
        UserValidate::checkInput($req);
        $res = UserService::register($req);
        Response::SendResponseJson($res['code'], $res['data']);
    } 
}
