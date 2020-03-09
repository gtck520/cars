<?php

namespace app\service;

use app\helper\Helper;
use app\model\Shop as ShopModel;

class Shop
{
    //列表
    public static function getList()
    {
        $field = ['id', 'name'];
        $res = ShopModel::field($field)->get();
        return ['code' => 200, 'data' => $res];
    }

    //添加
    public static function add($admin_id, $req)
    {
        $name = $req['name'];
        $address = $req['address'];

        $res = ShopModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "$name 已存在"];
        }

        $id = ShopModel::insert([
            'name' => $name,
            'address' => $address,
        ]);

        Helper::saveToLog($admin_id, '', '', $name, "管理员ID:$id 添加门店 [$name][$address]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $name = $req['name'];
        $address = $req['address'];

        $res = ShopModel::where(['name' => $name])->find();
        if ($res) {
            return ['code' => 400, 'data' => "$name 已存在"];
        }
        //
        $old_value = ShopModel::field(['name'])->where(['id' => $id])->find();
        ShopModel::where(['id' => $id])->update([
            'name' => $name,
            'address' => $address,
        ]);
        //

        Helper::saveToLog($admin_id, '',$old_value['name'], $name, "管理员ID:$admin_id 修改门店ID:$id [{$old_value['name']} 为 $name][$address]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = ShopModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此门店'];
        }
        ShopModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除门店ID: $id [{$res['name']}]");
        return ['code' => 200, 'data' => ''];
    }
}
