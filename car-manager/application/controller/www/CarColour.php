<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\CarColour as CarColourService;
use app\validate\CarColour as CarColourValidate;

class CarColour extends AdminController
{
    //列表
    public function getList(){
        $res = CarColourService::getList();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加
    public function add(){
        $req = P();
        CarColourValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = CarColourService::add($admin_id ,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($id){
        $req = json_decode(put(), true);
        CarColourValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = CarColourService::modify($admin_id ,$id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($id){
        $admin_id = parent::$admin_id;
        $res = CarColourService::delete($admin_id ,$id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
