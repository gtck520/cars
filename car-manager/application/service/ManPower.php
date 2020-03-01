<?php

namespace app\service;

use app\helper\Helper;
use app\model\Admin as AdminModel;
use app\model\ManPower as ManPowerModel;

class ManPower
{
    //列表
    public static function getList($req)
    {
        $c = $req['c'] ?? 10;
        $p = $req['p'] ?? 1;
        $field = ['id', 'controller', 'action', 'powername', 'sort', 'creator', 'create_time'];
        $orderby = ['sort' => 'desc'];
        $res = ManPowerModel::field($field)->orderby($orderby)->page($c, $p);
        return ['code' => 200, 'data' => Helper::formatTimt($res)];
    }

    //添加
    public static function add($admin_id, $req)
    {
        $controller = $req['controller'];
        $action = $req['action'];
        $powername = $req['powername'];
        $sort = $req['sort'];

        $res = ManPowerModel::where(['controller' => $controller, 'action' => $action])->find();
        if ($res) {
            return ['code' => 400, 'data' => "控制器:$controller 已存在方法:$action "];
        }

        $admin_name = AdminModel::where(['id' => $admin_id])->value('name');
        if (empty($admin_name)) {
            return ['code' => 400, 'data' => "异常调用"];
        }
        $id = ManPowerModel::insert([
            'controller' => $controller,
            'action' => $action,
            'powername' => $powername,
            'sort' => $sort,
            'creator' => $admin_name,
            'create_time' => time()
        ]);

        Helper::saveToLog($admin_id, '', '', '', "添加权限ID:$id [$controller:$action:$powername:$sort]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $controller = $req['controller'];
        $action = $req['action'];
        $powername = $req['powername'];
        $sort = $req['sort'];

        $res = ManPowerModel::where(['controller' => $controller, 'action' => $action])->find();
        if ($res) {
            return ['code' => 400, 'data' => "控制器:$controller 已存在方法:$action "];
        }
        //
        $old_value = ManPowerModel::field(['controller', 'action', 'powername', 'sort'])->where(['id' => $id])->get();

        ManPowerModel::where(['id' => $id])->update([
            'controller' => $controller,
            'action' => $action,
            'powername' => $powername,
            'sort' => $sort,
        ]);
        //
        Helper::saveToLog(
            $admin_id,
            '',
            "{$old_value['controller']}:{$old_value['action']}:{$old_value['powername']}:{$old_value['sort']}",
            "$controller:$action:$powername:$sort",
            "修改权限ID:$id [{$old_value['controller']}:{$old_value['action']}:{$old_value['powername']}:{$old_value['sort']} 为 $controller:$action:$powername:$sort]"
        );
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = ManPowerModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此权限'];
        }
        ManPowerModel::delete(['id' => $id]);

        Helper::saveToLog($admin_id, '', '', '', "删除权限ID:$id [{$res['powername']}:{$res['controller']}:{$res['action']}]");
        return ['code' => 200, 'data' => ''];
    }
}
