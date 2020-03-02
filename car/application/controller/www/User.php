<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\User as UserService;
use app\validate\User as UserValidate;

class User extends UserController
{
    public function userInfo(){
        $user_id = self::$user_id;
        $res = UserService::userInfo($user_id);
        Response::SendResponseJson($res['code'], $res['data']);
    } 

    public function enshrine($car_id){
        $user_id = self::$user_id;
        $res = UserService::enshrine($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
    public function enshrineDel($car_id){
        $user_id = self::$user_id;
        $res = UserService::enshrineDel($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    public function browseDel($car_id){
        $user_id = self::$user_id;
        $res = UserService::browseDel($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
}
