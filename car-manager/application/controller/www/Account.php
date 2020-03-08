<?php

namespace app\controller\www;

use king\lib\Response;
use app\validate\Common;
use app\service\Account as AccountService;

class Account extends AdminController
{

    // 获得会员缴费
    public function getPayList(){
        $req = G();
        Common::checkPage($req);
        $res = AccountService::getCostList($req,0);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    // 获得会员推荐奖励
    public function getInviteList(){
        $req = G();
        Common::checkPage($req);
        $res = AccountService::getCostList($req,1);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
