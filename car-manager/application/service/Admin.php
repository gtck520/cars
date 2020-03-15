<?php

namespace app\service;

use app\helper\Helper;
use app\model\Admin as AdminModel;
use app\model\ManRole as ManRoleModel;

class Admin
{
    //列表
    public static function getList($req)
    {
        $orderby = ['id' => 'desc'];
        $res = AdminModel::field('id,name,mobile')->orderby($orderby)->page($req['c'], $req['p']);
        return ['code' => 200, 'data' => $res];
    }
    //获取下拉
    public static function getAdmins()
    {
        $res = AdminModel::field('id,name,mobile')->get();
        return ['code' => 200, 'data' => $res];
    }
    //添加
    public static function add($admin_id, $name, $mobile, $password, $rid)
    {
        $res = AdminModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "名称: $name 已存在"];
        }
        $res = AdminModel::where(['mobile' => $mobile])->find();
        if ($res) {
            return ['code' => 400, 'data' => "手机号码: $mobile 已存在"];
        }

        $rid_res = ManRoleModel::where(['id' => $rid])->find();
        if (!$rid_res) {
            return ['code' => 400, 'data' => "无此组"];
        }
        //
        $password = Helper::getPassword($password);
        $id = AdminModel::insert([
            'name' => $name,
            'mobile' => $mobile,
            'password' => $password,
            'rid'   => $rid,
            'created_time' => time(),
        ]);

        Helper::saveToLog($admin_id, '', '', $name, "添加管理员ID:$id [$name]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($__admin_id, $admin_id, $name, $mobile, $password, $rid)
    {
        $res = AdminModel::where(['name' => $name, 'id <>' => $admin_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "姓名: $name 已存在"];
        }
        $res = AdminModel::where(['mobile' => $mobile, 'id <>' => $admin_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "手机号码: $mobile 已存在" . $admin_id];
        }
        $rid_res = ManRoleModel::where(['id' => $rid])->find();
        if (!$rid_res) {
            return ['code' => 400, 'data' => "无此组"];
        }
        //
        $old_value = AdminModel::field(['name', 'mobile'])->where(['id' => $admin_id])->find();

        AdminModel::where(['id' => $admin_id])->update([
            'name' => $name,
            'mobile' => $mobile,
            'rid'   => $rid,
            'password' => Helper::getPassword($password),
        ]);
        //
        
        Helper::saveToLog($__admin_id, '', "{$old_value['name']}:{$old_value['mobile']}", "$name:$mobile", "修改管理员ID:$admin_id [{$old_value['name']}:{$old_value['mobile']} 为 $admin_id:$name:$mobile]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($__admin_id, $admin_id)
    {
        $old_value = AdminModel::field(['name'])->where(['id' => $admin_id])->find();
        if ($old_value) {
            AdminModel::delete(['id' => $admin_id]);
            Helper::saveToLog($__admin_id, '', '', '', "删除管理员ID:$admin_id [{$old_value['name']}]");
        }
        return ['code' => 200, 'data' => ''];
    }
}
