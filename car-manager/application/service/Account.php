<?php

namespace app\service;

use app\helper\Helper;
use app\model\Cost as CostModel;

class Account
{
    public static function getCostList($req,$type)
    {
        $query = CostModel::setTable('cost c')->join('user u', 'c.user_id = u.id');
        //发布人人号码
        if (!empty($req['mobile'])) {
            $query->where('u.mobile', '=', $req['mobile']);
        }
        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('u.province_id', '=', $req['province_id']);
        }
        if (!empty($req['city_id']) ) {
            $query->where('u.city_id', '=', $req['city_id']);
        }
        if (!empty($req['area_id'])) {
            $query->where('u.area_id', '=', $req['area_id']);
        }
        $query->where('c.type', '=', $type);
        $car_list = $query->field(["c.*","u.realname","u.mobile"])->page($req['c'], $req['p']);
        foreach ($car_list['rs'] as $key=>$value){
            $car_list['rs'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        return ['code' => 200, 'data' => $car_list];
    }



}
