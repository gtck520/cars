<?php

namespace app\controller\www;

use king\lib\Response;
use app\validate\ManPower as ManPowerValidate;
use app\service\ManPower as ManPowerService;

class ManPower extends AdminController
{
    //列表
    public function getList()
    {
        $req = G();
        ManPowerValidate::checkPage($req);
        $res = ManPowerService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //添加
    public function add()
    {
        $req = P();
        $admin_id = parent::$admin_id;
        ManPowerValidate::checkInput($req);
        $res = ManPowerService::add($admin_id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //修改
    public function modify($id)
    {
        $req = json_decode(put(), true);
        ManPowerValidate::checkInput($req);
        $admin_id = parent::$admin_id;
        $res = ManPowerService::modify($admin_id, $id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //删除
    public function delete($id)
    {
        $admin_id = parent::$admin_id;
        $res = ManPowerService::delete($admin_id, $id);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
