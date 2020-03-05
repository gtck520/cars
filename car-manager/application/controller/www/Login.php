<?php

namespace app\controller\www;

use king\lib\Response;
use app\cache\Token;
use app\service\Login as LoginService;
use app\validate\Login as LoginValidate;

class Login
{
    /**
     * @OA\Post(
     *     path="/admin/logins",
     *     tags={"用户管理"},
     *     summary="登录",
     *     @OA\Parameter(name="Content-Type",in="header",example="application/x-www-form-urlencoded",required=true),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="mobile",example="18695732896",type="string",description="电话号码"),
     *                 @OA\Property(property="password",example="prw123",type="string",description="密码")
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
    public function login(){
        $req = P();
        LoginValidate::checkInput($req);
        $res = LoginService::login($req['mobile'], $req['password']);
        Response::SendResponseJson($res['code'], $res['data']);
    }

    //验证token
    public function checkToken(){
        $token = H('token');
        if (!$token || empty($token)) {
            Response::SendResponseJson(400, '验证失败');
        };
        //本地缓存中获取
        $admin_id = Token::get($token);
        if (!is_numeric($admin_id)) {
            Response::SendResponseJson(400, '验证失败');
        }
            
        Response::SendResponseJson(200, '');
    }
    
}
