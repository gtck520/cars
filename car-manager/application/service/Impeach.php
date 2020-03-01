<?php

namespace app\service;

use app\helper\Helper;
use app\model\Car as CarModel;
use app\model\Impeach as ImpeachModel;

class Impeach
{
    //列表
    public static function getList()
    {
        $field = ['id', 'user_id', 'car_id', 'notes', 'create_time'];
        $res = ImpeachModel::field($field)->get();
        foreach ($res as $key => &$value) {
            $value['car_info'] = CarModel::where(['id' => $value['car_id']])->find();
            //此处车辆详情 需要讨论
        }
        return ['code' => 200, 'data' => Helper::formatTimt($res)];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $name = $req['name'];

        $res = ImpeachModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "$name 已存在"];
        }
        //
        $old_value = ImpeachModel::field(['name'])->where(['id' => $id])->find();
        ImpeachModel::where(['id' => $id])->update([
            'name' => $name,
        ]);
        //

        Helper::saveToLog($admin_id, '',$old_value['name'], $name, "管理员ID:$admin_id 修改车辆颜色ID:$id [{$old_value['name']} 为 $name]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = ImpeachModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此角色'];
        }
        ImpeachModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除车辆颜色ID: $id [{$res['name']}]");
        return ['code' => 200, 'data' => ''];
    }
}
