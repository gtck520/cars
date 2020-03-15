<?php

namespace app\service;

use app\helper\Helper;
use app\model\ManRole as ManRoleModel;
use app\model\ManPower as ManPowerModel;

class Group
{
    //列表
    public static function getList()
    {
        $field = ['id', 'powerid', 'rolename', 'admin_id', 'notes', 'create_time'];
        $man_role_list = ManRoleModel::field($field)->get();
        foreach ($man_role_list as &$value) {
            if ($value['powerid'] != 'ALL') {
                $powerid_arr = explode('|', trim($value['powerid'], '|'));
                $value['power'] = ManPowerModel::field(['id', 'powername'])->where(['id in' => $powerid_arr])->get();
            }else{
                $value['power'] = ManPowerModel::field(['id', 'powername'])->get();
            }
            
            unset($value['powerid']);
        }
        return ['code' => 200, 'data' => Helper::formatTimt($man_role_list)];
    }
    //获取下拉
    public static function getGroups()
    {
        $res = ManRoleModel::field('id,rolename')->get();
        return ['code' => 200, 'data' => $res];
    }
    //添加
    public static function add($admin_id, $req)
    {
        $powerid = $req['powerid'];
        $rolename = $req['rolename'];
        $notes = $req['notes'];
        $res = ManRoleModel::where(['rolename' => $rolename])->find();
        if ($res) {
            return ['code' => 400, 'data' => "角色:$rolename 已存在"];
        }

        
        $id = ManRoleModel::insert([
            'powerid' => $powerid,
            'rolename' => $rolename,
            'admin_id' => $admin_id,
            'notes'  => $notes,
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
        $notes = $req['notes'];

        $res = ManRoleModel::where(['rolename' => $rolename, 'id <>' => $id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "角色:$rolename 已存在"];
        }
        //
        $old_value = ManRoleModel::field(['powerid', 'rolename'])->where(['id' => $id])->find();
        ManRoleModel::where(['id' => $id])->update([
            'powerid' => $powerid,
            'rolename' => $rolename,
            'notes'  => $notes,
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
