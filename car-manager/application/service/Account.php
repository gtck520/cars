<?php

namespace app\service;

use app\helper\Helper;
use app\model\Cost as CostModel;
use app\model\MoneyRecord as MoneyRecordModel;

class Account
{
    //缴费与推荐奖励记录
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

    //资金明细记录
    public static function  getMoneyRecords($req){
        $query = MoneyRecordModel::setTable('user_money_records c')->join('user u', 'c.user_id = u.id');
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
        if (!empty($req['type'])) {
            $query->where('c.type', '=', $req['type']);
        }
        $car_list = $query->field(["c.*","u.realname","u.mobile"])->page($req['c'], $req['p']);
        $type_dict=[
            0=>'无变化',
            1=>'充值',
            2=>'提现',
            3=>'查询',
            4=>'退款'
        ];
        foreach ($car_list['rs'] as $key=>$value){
            $car_list['rs'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $car_list['rs'][$key]['type_msg'] = $type_dict[$value['type']];
        }
        return ['code' => 200, 'data' => $car_list];
    }




}
