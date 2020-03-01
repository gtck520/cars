<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Query as QueryService;
use app\validate\Common;

class Query extends UserController
{
    /**
     * @OA\Post(
     *     path="/query/getpay",
     *     tags={"车辆查询"},
     *     summary="单次费用及余额查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="type",type="string",description="查询类型-- maintenance：维保查询|collision：碰撞查询|vehicleCondition：汽车状态|regulations：违章查询|smallUnion：小综合|bigUnion：大综合，"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功",
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/query_getpay"),
     *          ),
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function getPay(){
        $req = P();
        if(empty($req['type']))
        {
            return ['code' => 400, 'data' => "查询类型不能为空"];
        }
        //Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::getPay($req,$req['type']);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    /**
     * @OA\Post(
     *     path="/query/maintenance",
     *     tags={"车辆查询"},
     *     summary="维保查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
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
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/query_getpay"),
     *          ),
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function maintenance(){

        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"maintenance");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //碰撞查询
    public function collision(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"collision");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //汽车状态查询
    public function vehicleCondition(){
        $req = P();
        Common::checkVin($req);
        $req['engine']= $req['engine'] ?? '';
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"vehicleCondition");
        Response::SendResponseJson($res['code'], $res['data']);

    }
    //违章查询
    public function regulations(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::regulations($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //小综合（维保+碰撞）查询
    public function smallUnion(){
        $req = P();
        Common::checkVin($req);
        $req['engine']= $req['engine'] ?? '';
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"smallUnion");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //大综合（维保+碰撞+汽车状态）
    public function bigUnion(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"bigUnion");
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
