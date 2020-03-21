<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Index as IndexService;


class Index extends AdminController
{
    /**
     * @OA\Get(
     *     path="/index",
     *     tags={"首页管理"},
     *     summary="获取首页统计值",
     *     @OA\Parameter(name="Authorization",in="header",description="管理用户权限",required=true),
     *     @OA\Response(response=200,description="OK"),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function index(){
        $res=IndexService::getIndex();
        Response::SendResponseJson($res['code'], $res['data']);
    }

}
