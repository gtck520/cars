<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\User as UserService;
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

     //修改文本
     public function update(){
        $req = json_decode(Put(), true);
        if (!isset($req['User'])) {
            Response::SendResponseJson(400, '文本字段未定义');
        }
        $admin_id = parent::$admin_id;
        $res = UserService::update($admin_id, $req['User']);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
