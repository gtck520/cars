<?php

namespace app\service;

use app\helper\Helper;
use app\model\ManRole as ManRoleModel;

class Group
{
    //列表
    public static function getList()
    {
        $field = ['id', 'powerid', 'rolename', 'admin_id', 'create_time'];
        $res = ManRoleModel::field($field)->get();
        return ['code' => 200, 'data' => Helper::formatTimt($res)];
    }

    //添加
    public static function add($admin_id, $req)
    {
        $powerid = $req['powerid'];
        $rolename = $req['rolename'];

        $res = ManRoleModel::where(['rolename' => $rolename])->find();
        if ($res) {
            return ['code' => 400, 'data' => "角色:$rolename 已存在"];
        }

        $id = ManRoleModel::insert([
            'powerid' => $powerid,
            'rolename' => $rolename,
            'admin_id' => $admin_id,
            'create_time' => time()
        ]);

        Helper::saveToLog($admin_id, '', '', $rolename, "添加角色ID:$id [$rolename]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $powerid = $req['powerid'];
        $rolename = $req['rolename'];

        $res = ManRoleModel::where(['rolename' => $rolename])->find();
        if ($res) {
            return ['code' => 400, 'data' => "角色:$rolename 已存在"];
        }
        //
        $old_value = ManRoleModel::field(['powerid', 'rolename'])->where(['id' => $id])->find();
        ManRoleModel::where(['id' => $id])->update([
            'powerid' => $powerid,
            'rolename' => $rolename,
        ]);
        //

        Helper::saveToLog($admin_id, '', "{$old_value['powerid']}:{$old_value['rolename']}", "$powerid:$rolename", "修改角色ID:$id [{$old_value['powerid']}:{$old_value['rolename']} 为 $powerid:$rolename]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = ManRoleModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此角色'];
        }
        ManRoleModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "删除角色ID:$id [{$res['rolename']}]");
        return ['code' => 200, 'data' => ''];
    }
}
