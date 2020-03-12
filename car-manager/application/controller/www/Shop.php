<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Shop as ShopService;
use app\validate\Shop as ShopValidate;

class Shop extends AdminController
{
    //列表
    public function getList(){
        $req = G();
        $res = ShopService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加
    public function add(){
        $req = P();
        ShopValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = ShopService::add($admin_id ,$req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($id){
        $req = json_decode(put(), true);
        ShopValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = ShopService::modify($admin_id ,$id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($id){
        $admin_id = parent::$admin_id;
        $res = ShopService::delete($admin_id ,$id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
