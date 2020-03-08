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
    
    //删除收藏
    public function enshrineDel($car_id){
        $user_id = self::$user_id;
        $res = UserService::enshrineDel($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除浏览
    public function browseDel($car_id){
        $user_id = self::$user_id;
        $res = UserService::browseDel($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //车辆出价  
    public function addPrice(){
        $req = P();
        $user_id = self::$user_id;
        $res = UserService::addPrice($user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 充值余额
    public function recharge()
    {
        $req = P();
        UserValidate::rechargeValidate($req);
        UserService::recharge(parent::$user_id, $req);
    }

    //发布的车
    public function Cars()
    {
        $req = G();
        if (!in_array($req['status'],['-1','1', '0'])) {
            Response::SendResponseJson(400, 'status 不正确');
        }
        $user_id = self::$user_id;
        $res = UserService::Cars($user_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加电话量
    public function addPhoneNum($car_id)
    {
        $user_id = self::$user_id;
        $res = UserService::addPhoneNum($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //获取出价记录
    public function getPrice()
    {
        $user_id = self::$user_id;
        $res = UserService::getPrice($user_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
