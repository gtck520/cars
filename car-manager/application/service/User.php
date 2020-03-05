<?php

namespace app\service;

use app\helper\Helper;
use app\model\User as UserModel;
use app\model\Car as CarModel;

class User
{
    //列表
    public static function getList($req)
    {
        $orderby = ['last_login_time' => 'desc'];
        $res = UserModel::orderby($orderby)->page($req['c'], $req['p']);
        return ['code' => 200, 'data' => Helper::formatTimt($res, ['create_time', 'last_login_time'])];
    }

    //用户详情
    public static function getUserInfo($user_id)
    {
        $res = UserModel::where(['user_id' => $user_id])->find();
        return ['code' => 200, 'data' => Helper::formatTimt($res, ['create_time', 'last_login_time'])];
    }

    //修改
    public static function modify($user_id, $id, $req)
    {
        $controller = $req['controller'];
        $action = $req['action'];
        $powername = $req['powername'];
        $sort = $req['sort'];

        $res = UserModel::where(['controller' => $controller, 'action' => $action, 'id <>' => $id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "控制器:$controller 已存在方法:$action "];
        }
        //
        $old_value = UserModel::field(['controller', 'action', 'powername', 'sort'])->where(['id' => $id])->find();

        UserModel::where(['id' => $id])->update([
            'controller' => $controller,
            'action' => $action,
            'powername' => $powername,
            'sort' => $sort,
        ]);
        //
        Helper::saveToLog(
            $user_id,
            '',
            "{$old_value['controller']}:{$old_value['action']}:{$old_value['powername']}:{$old_value['sort']}",
            "$controller:$action:$powername:$sort",
            "修改权限ID:$id [{$old_value['controller']}:{$old_value['action']}:{$old_value['powername']}:{$old_value['sort']} 为 $controller:$action:$powername:$sort]"
        );
        return ['code' => 200, 'data' => ''];
    }

}
