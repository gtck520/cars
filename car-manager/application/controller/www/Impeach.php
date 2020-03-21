<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Impeach as ImpeachService;
use app\validate\Impeach as ImpeachValidate;

class Impeach extends AdminController
{
    //列表
    public function getList(){
        $req = G();
        $req=parent::checkAgent($req);
        $res = ImpeachService::getList($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //处理举报信息
    public function modify($id){
        $req = json_decode(put(), true);
        $admin_id = parent::$admin_id;
        $res = ImpeachService::modify($admin_id ,$id, $req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

}
