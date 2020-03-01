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
    /**
     * @OA\Post(
     *     path="/query/getpay",
     *     tags={"车辆查询"},
     *     summary="单次费用及余额查询",
     *     @OA\Parameter(name="Authorization",in="header",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",description="车架号"),
     *                 example={"vin": "sadf656s4df6465"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功",
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
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
