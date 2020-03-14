<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\User as UserService;
use app\service\UserLevel as UserLevelService;
use app\validate\User as UserValidate;

class User extends AdminController
{
    // 用户列表
    public function getList(){
        $req = G();
        UserValidate::checkPage($req);
        $res = UserService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    // 用户详情
    public function getUserInfo($user_id){
        $res = UserService::getUserInfo($user_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }

     //修改用户
     public function update($user_id){
        $req = json_decode(Put(), true);
        $admin_id = parent::$admin_id;
        $res = UserService::modify($admin_id,$user_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //获取等级列表
    public function getLevelList(){
        $res = UserLevelService::get();
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //修改等级相关规则
    public function updateLevel($id){
        $req = json_decode(Put(), true);
        if(empty($req)){
            Response::SendResponseJson(400, '未做任何修改');
        }
        $admin_id = parent::$admin_id;
        $res = UserLevelService::update($admin_id,$id,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
