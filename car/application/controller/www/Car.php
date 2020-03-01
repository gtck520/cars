<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Car as CarService;
use app\validate\Car as CarValidate;

class Car
{
    // 获取车的列表
    public function getList(){
        $req = G();
        dd($req);
        // CarValidate::checkInput($req);
        $res = CarService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 获取品牌列表
    public function getCarName()
    {
        $res = CarService::getCarName();
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
        $res = CarService::getCarInfo($car_id);
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
        $res = CarService::add($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
