<?php

namespace app\controller\www;

use king\lib\Response;
use app\controller\www\AdminController;
use app\service\Admin as AdminService;
use app\validate\Admin as AdminValidate;


class Admin extends AdminController
{
    /**
     * @OA\Info(title="车塘小程序-后台管理API", version="0.1")
     */
    //列表
    public function getList(){
        $req=G();
        $req['c']= $req['c'] ?? 10;
        $req['p']= $req['p'] ?? 1;
        $res = AdminService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //获取所有管理员下拉
    public function getAdmins(){
        $res = AdminService::getAdmins();
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加
    public function add(){
        $req = P();
        AdminValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = AdminService::add($admin_id ,$req['name'], $req['mobile'], $req['password'], $req['rid']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($admin_id){
        $req = json_decode(put(), true);
        AdminValidate::checkInput($req);
        $__admin_id = parent::$admin_id;
        $res = AdminService::modify($__admin_id ,$admin_id, $req['name'], $req['mobile'], $req['password'], $req['rid']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($admin_id){
        $__admin_id = parent::$admin_id;
        if ($admin_id == $__admin_id) {
            Response::SendResponseJson(400, '不能删除自己');
        }
        $res = AdminService::delete($__admin_id ,$admin_id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
}
