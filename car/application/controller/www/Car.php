<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Car as CarService;
use app\validate\Car as CarValidate;

class Car extends UserController
{
    //获取车的列表
    public function getList(){
        $req = G();
        CarValidate::checkPage($req);
        CarValidate::searchInput($req);
        $res = CarService::getList(parent::$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 获取品牌列表
    public function getCarName()
    {
        $res = CarService::getCarName();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 获取所有车辆类型
    public function getCarType()
    {
        $res = CarService::getCarType();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 获取所有车辆变速箱
    public function getCarBS()
    {
        $res = CarService::getCarBS();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 更新缓存
    public function setCache()
    {
        $res = CarService::setCache();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 车辆详情
    public function getCarInfo($car_id)
    {
        $user_id = parent::$user_id;
        $res = CarService::getCarInfo($user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //举报
    public function impeach($car_id)
    {
        $res = UserController::loginId();
        if ($res) {
            $user_id = parent::$user_id;
        }else{
            $user_id = '';
        }

        $req = P();
        if (!isset($req['type_id'])) {
            Response::SendResponseJson(400, '举报类型未定义!');
        }
        $res = CarService::impeach($user_id, $car_id, $req['type_id']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 车辆添加
    public function add(){
        $req = P();
        CarValidate::checkInput($req);
        $user_id = parent::$user_id;
        $res = CarService::add($user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //浏览记录列表
    public function getCarBrowseList(){
        $req = G();
        CarValidate::checkPage($req);
        $res = CarService::getCarBrowseList(parent::$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

     //收藏记录列表
     public function getCarEnshrinesList(){
        $req = G();
        CarValidate::checkPage($req);
        $res = CarService::getCarEnshrinesList(parent::$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加收藏
    public function addEnshrines($car_id)
    {
        $res = CarService::addEnshrines(parent::$user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //车辆颜色
    public function getColourList()
    {
        Response::SendResponseJson(200, CarService::getColourList());
    }

     //车源标签
     public function getCheyuanList()
     {
         Response::SendResponseJson(200, CarService::getCheyuanList());
     }

    //添加帮卖
    public function addBM($car_id)
    {
        $res = CarService::addBM(parent::$user_id, $car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //收藏记录列表
    public function getCarBMList()
    {
        $req = G();
        CarValidate::checkPage($req);
        $res = CarService::getCarBMList(parent::$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

}
