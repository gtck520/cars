<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Login as LoginService;
use app\validate\Login as LoginValidate;
use app\service\User as UserService;
use app\validate\User as UserValidate;

class Login
{
    /**
     * @OA\Post(
     *     path="/logins",
     *     tags={"用户中心"},
     *     summary="用户登录",
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="code",type="string",description="编码"),
     *                 @OA\Property(property="mobile",type="string",description="手机号"),
     *                 @OA\Property(property="avatar",type="string",description="头像"),
     *                 @OA\Property(property="nickname",type="string",description="昵称"),
     *                 example={"vin": "sadf656s4df6465","mobile": "18695732896","avatar": "123.jpg","vin": "老牛逼了"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功",
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/login_easylogin"),
     *          ),
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function login(){
        $req = P();
        LoginValidate::checkInput($req);
        $res = LoginService::login($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    /**
     * @OA\Post(
     *     path="/easylogin",
     *     tags={"用户中心"},
     *     summary="便捷登录",
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="openid",type="string",example="10086",description="openid")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="请求成功",
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/login_easylogin"),
     *          ),
     *     ),
     *     @OA\Response(response=401,description="权限验证失败"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function easylogin(){
        $req = P();
        if(empty($req['openid'])){
            return ['code' => 400, 'data' => "openid不能为空"];
        }
        $res = LoginService::easylogin($req);
        Response::SendResponseJson($res['code'], $res['data']);
    }
    public function register(){
        $req = P();
        UserValidate::checkInput($req);
        $res = UserService::register($req);
        Response::SendResponseJson($res['code'], $res['data']);
    } 
}
