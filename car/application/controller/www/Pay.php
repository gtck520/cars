<?php

namespace app\controller\www;

use app\service\Pay as PayService;
use king\lib\Response;
use app\validate\Pay as PayValidate;

class Pay
{
    //支付宝支付回调
    public function wechatNotify()
    {
        echo PayService::notify('wechat');
    }

    //订单查询
    public function find()
    {
        $pay_trade_no = G('pay_trade_no', '');

        $res = PayService::find($pay_trade_no, 'wechat', 'miniapp');
        Response::sendResponseJson($res['code'], $res['data']);
    }

    //订单退款
    public function refund()
    {
        $req = P();
        PayValidate::checkInput($req);
        $res = PayService::refund($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

}