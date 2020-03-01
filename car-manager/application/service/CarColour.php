<?php

namespace app\service;

use app\helper\Helper;
use app\model\CarColour as CarColourModel;

class CarColour
{
    //列表
    public static function getList()
    {
        $field = ['id', 'name'];
        $res = CarColourModel::field($field)->get();
        return ['code' => 200, 'data' => $res];
    }

    //添加
    public static function add($admin_id, $req)
    {
        $name = $req['name'];

        $res = CarColourModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "$name 已存在"];
        }

        $id = CarColourModel::insert([
            'name' => $name,
        ]);

        Helper::saveToLog($admin_id, '', '', $name, "管理员ID:$id 添加车辆颜色 [$name]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $name = $req['name'];

        $res = CarColourModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "$name 已存在"];
        }
        //
        $old_value = CarColourModel::field(['name'])->where(['id' => $id])->find();
        CarColourModel::where(['id' => $id])->update([
            'name' => $name,
        ]);
        //

        Helper::saveToLog($admin_id, '',$old_value['name'], $name, "管理员ID:$admin_id 修改车辆颜色ID:$id [{$old_value['name']} 为 $name]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = CarColourModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此角色'];
        }
        CarColourModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除车辆颜色ID: $id [{$res['name']}]");
        return ['code' => 200, 'data' => ''];
    }
}
