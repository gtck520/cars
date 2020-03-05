<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Set as SetService;

class Set extends AdminController
{
    /**
     * @OA\Post(
     *     path="/admin/cityarea",
     *     tags={"会员管理"},
     *     summary="城市筛选接口",
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="pid",type="int",description="父节点ID"),
     *                 example={"pid": 352200}
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
    public function getCity(){
        $req = P();
        $res=SetService::getCity($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
