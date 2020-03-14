<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\AgentCity as AgentCityService;
use app\validate\Common as CommonValidate;

class AgentCity extends AdminController
{
    //列表
    public function getList(){
        $req=G();
        CommonValidate::checkPage($req);
        $res = AgentCityService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加
    public function add(){
        $req = P();
        $admin_id = parent::$admin_id;
        $res = AgentCityService::add($admin_id ,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($id){
        $req = json_decode(put(), true);
        $admin_id = parent::$admin_id;
        $res = AgentCityService::modify($admin_id ,$id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($id){
        $admin_id = parent::$admin_id;
        $res = AgentCityService::delete($admin_id ,$id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
