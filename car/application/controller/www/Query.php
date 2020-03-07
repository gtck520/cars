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
     *                 @OA\Property(property="vin",type="string",example="LB37722Z1JH072318",description="车架号")
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
    /**
     * @OA\Post(
     *     path="/query/collision",
     *     tags={"车辆查询"},
     *     summary="碰撞查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",example="LB37722Z1JH072318",description="车架号")
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
    public function collision(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"collision");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //汽车状态查询
    /**
     * @OA\Post(
     *     path="/query/vehicleCondition",
     *     tags={"车辆查询"},
     *     summary="汽车状态查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",example="LB37722Z1JH072318",description="车架号")
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
    public function vehicleCondition(){
        $req = P();
        Common::checkVin($req);
        $req['engine']= $req['engine'] ?? '';
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"vehicleCondition");
        Response::SendResponseJson($res['code'], $res['data']);

    }
    //违章查询
    /**
     * @OA\Post(
     *     path="/query/regulations",
     *     tags={"车辆查询"},
     *     summary="违章查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",example="LB37722Z1JH072318",description="车架号"),
     *                 @OA\Property(property="hpzl",type="string",example="02",description="号牌种类"),
     *                 @OA\Property(property="hphm",type="string",example="闽JJP600",description="车架号"),
     *                 @OA\Property(property="fdjh",type="string",example="J4CA0335669",description="发动机号")
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
    public function regulations(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::regulations($req);
        Response::SendResponseJson(200, $res);
    }

    //小综合（维保+碰撞）查询
    /**
     * @OA\Post(
     *     path="/query/smallUnion",
     *     tags={"车辆查询"},
     *     summary="小综合（维保+碰撞）查询",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",example="LB37722Z1JH072318",description="车架号")
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
    public function smallUnion(){
        $req = P();
        Common::checkVin($req);
        $req['engine']= $req['engine'] ?? '';
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"smallUnion");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    //大综合（维保+碰撞+汽车状态）
    /**
     * @OA\Post(
     *     path="/query/bigUnion",
     *     tags={"车辆查询"},
     *     summary="大综合（维保+碰撞+汽车状态）",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",example="LSVFD26R1B2722145",description="车架号")
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
    public function bigUnion(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::query($req,"bigUnion");
        Response::SendResponseJson($res['code'], $res['data']);
    }
    /**
     * @OA\Post(
     *     path="/query/getVin",
     *     tags={"车辆查询"},
     *     summary="扫码识别VIN",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="imgbase64",type="string",description="base64图片串"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功"
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function vinOcr(){
        $req = P();
        if(empty($req['imgbase64'])){
            return ['code' => 400, 'data' => "imgbase64图片错误"];
        }
        $req['user_id'] = parent::$user_id;
        $res = QueryService::vinOcr($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    /**
     * @OA\Post(
     *     path="/query/getCarInfo",
     *     tags={"车辆查询"},
     *     summary="VIN码获取车辆信息",
     *     @OA\Parameter(name="token",in="header",example="token_string",description="登录用户权限",required=true),
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="vin",type="string",description="车架号vin"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功"
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function vinGetinfo(){
        $req = P();
        Common::checkVin($req);
        $req['user_id'] = parent::$user_id;
        $res = QueryService::vinGetinfo($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
