<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Group as GroupService;
use app\validate\Group as GroupValidate;


class Group extends AdminController
{
    //列表
    public function getList(){
        $res = GroupService::getList();
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //下拉
    public function getGroups(){
        $res = GroupService::getGroups();
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //添加
    public function add(){
        $req = P();
        GroupValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = GroupService::add($admin_id ,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($id){
        $req = json_decode(put(), true);
        GroupValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = GroupService::modify($admin_id ,$id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($id){
        $admin_id = parent::$admin_id;
        $res = GroupService::delete($admin_id ,$id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
}
