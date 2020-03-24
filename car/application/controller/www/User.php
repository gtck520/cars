<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\User as UserService;
use app\validate\User as UserValidate;

class User extends UserController
{
    //注册
    public function register(){
        $req = P();
        UserValidate::checkInput($req);
        $res = UserService::register(parent::$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    } 

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

    //删除帮卖
    public function bmDel($car_id){
        $user_id = self::$user_id;
        $res = UserService::bmDel($user_id, $car_id);
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

    //发布的车  我的车源  
    public function Cars()
    {
        $req = G();
        if (!isset($req['status'])) {
            Response::SendResponseJson(400, 'status 未定义');
        }
        if (!in_array($req['status'],['-1','1', '0'])) {
            Response::SendResponseJson(400, 'status 不正确');
        }
        $user_id = self::$user_id;
        $res = UserService::Cars($user_id, $req['status']);
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

    //获取其他用户信息
    public function getUserInfo($user_id)
    {
        $res = UserService::getUserInfo($user_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }   
}
