<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Car as CarService;
use app\validate\Car as CarValidate;

class Car extends AdminController
{
    // 车辆列表
    public function getList(){
        $req = G();
        CarValidate::checkPage($req);
        $res = CarService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 车辆详情
    public function getCarInfo($Car_id){
        $res = CarService::getCarInfo($Car_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

     //修改文本
     public function update(){
        $req = json_decode(Put(), true);
        if (!isset($req['Car'])) {
            Response::SendResponseJson(400, '文本字段未定义');
        }
        $admin_id = parent::$admin_id;
        $res = CarService::update($admin_id, $req['Car']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改文本
    public function updateStatus($id){
        $req = json_decode(Put(), true);
        $admin_id = parent::$admin_id;
        $res = CarService::modifyStatus($admin_id,$id,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
