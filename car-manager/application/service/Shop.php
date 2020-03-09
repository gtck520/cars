<?php

namespace app\service;

use app\helper\Helper;
use app\model\CityArea as CityAreaModel;
use app\model\Shop as ShopModel;
use app\service\CityActive as CityActiveService;

class Shop
{
    //列表
    public static function getList($req)
    {

        $query=ShopModel::setTable('shop');
        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('province_id', '=', $req['province_id']);
        }
        if (!empty($req['city_id']) ) {
            $query->where('city_id', '=', $req['city_id']);
        }
        if (!empty($req['area_id'])) {
            $query->where('area_id', '=', $req['area_id']);
        }
        $res = $query->page($req['c'], $req['p']);
        foreach ($res['rs'] as $key=>$value){
            $res['rs'][$key]['create_time']=date('Y-m-d H:i:s',$value['create_time']);
            $current_id=CityActiveService::getCurrentCity($value);
            $res['rs'][$key]['city_fullname']=CityAreaModel::where(['id' => $current_id])->value(['fullname']);
        }
        return ['code' => 200, 'data' => $res];
    }
    //列表
    public static function getListCity()
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
