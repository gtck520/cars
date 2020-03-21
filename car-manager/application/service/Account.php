<?php

namespace app\service;

use app\helper\Helper;
use app\model\Cost as CostModel;
use app\model\MoneyRecord as MoneyRecordModel;
use app\model\QueryOrder as QueryOrderModel;

class Account
{
    //缴费与推荐奖励记录
    public static function getCostList($req,$type)
    {
        $query = CostModel::setTable('cost c')->join('user u', 'c.user_id = u.id');
        $where=[];
        //发布人人号码
        if (!empty($req['mobile'])) {
            $query->where('u.mobile', '=', $req['mobile']);
            $where['u.mobile']=$req['mobile'];
        }
        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('u.province_id', '=', $req['province_id']);
            $where['u.province_id']=$req['province_id'];
        }
        if (!empty($req['city_id']) ) {
            $query->where('u.city_id', '=', $req['city_id']);
            $where['u.city_id']=$req['city_id'];
        }
        if (!empty($req['area_id'])) {
            $query->where('u.area_id', '=', $req['area_id']);
            $where['u.area_id']=$req['area_id'];
        }
        $query->where('c.type', '=', $type);
        $where['c.type']=$type;
        $orderby = ['id' => 'desc'];
        $car_list = $query->field(["c.*","u.realname","u.mobile"])->order($orderby)->page($req['c'], $req['p']);

        //统计总金额
        $totalmoney=MoneyRecordModel::setTable('cost c')->join('user u', 'c.user_id = u.id')->where($where)->field(["sum(amount) as money"])->find();
        $car_list['totalmoney']=$totalmoney['money'];

        foreach ($car_list['rs'] as $key=>$value){
            $car_list['rs'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        return ['code' => 200, 'data' => $car_list];
    }

    //资金明细记录
    public static function  getMoneyRecords($req){
        $query = MoneyRecordModel::setTable('user_money_records c')->join('user u', 'c.user_id = u.id');
        //发布人人号码
        $where=[];
        if (!empty($req['mobile'])) {
            $query->where('u.mobile', '=', $req['mobile']);
            $where['u.mobile']=$req['mobile'];
        }
        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('u.province_id', '=', $req['province_id']);
            $where['u.province_id']=$req['province_id'];
        }
        if (!empty($req['city_id']) ) {
            $query->where('u.city_id', '=', $req['city_id']);
            $where['u.city_id']=$req['city_id'];
        }
        if (!empty($req['area_id'])) {
            $query->where('u.area_id', '=', $req['area_id']);
            $where['u.area_id']=$req['area_id'];
        }
        if (!empty($req['type'])) {
            $query->where('c.type', '=', $req['type']);
            $where['c.type']=$req['type'];
        }
        $orderby = ['id' => 'desc'];
        $car_list = $query->field(["c.*","u.realname","u.mobile"])->order($orderby)->page($req['c'], $req['p']);
        //统计总金额
        $totalmoney=MoneyRecordModel::setTable('user_money_records c')->join('user u', 'c.user_id = u.id')->where($where)->field(["sum(amount) as money"])->find();
        $car_list['totalmoney']=$totalmoney['money'];
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

    //查询记录
    public static function  getQueryList($req){
        $query = QueryOrderModel::setTable('query_order c')->join('user u', 'c.user_id = u.id');
        $where=[];
        //发布人人号码
        if (!empty($req['mobile'])) {
            $query->where('u.mobile', '=', $req['mobile']);
            $where['u.mobile']=$req['mobile'];
        }
        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('u.province_id', '=', $req['province_id']);
            $where['u.province_id']=$req['province_id'];
        }
        if (!empty($req['city_id']) ) {
            $query->where('u.city_id', '=', $req['city_id']);
            $where['u.city_id']=$req['city_id'];
        }
        if (!empty($req['area_id'])) {
            $query->where('u.area_id', '=', $req['area_id']);
            $where['u.area_id']=$req['area_id'];
        }
        if (isset($req['type'])&&$req['type']!='') {
            $query->where('c.type', '=', $req['type']);
            $where['c.type']=$req['type'];
        }
        $orderby = ['id' => 'desc'];
        $car_list = $query->field(["c.*","u.realname","u.mobile"])->order($orderby)->page($req['c'], $req['p']);

        $totalmoney=QueryOrderModel::setTable('query_order c')->join('user u', 'c.user_id = u.id')->where($where)->field(["sum(cost) as money"])->find();
        $car_list['totalmoney']=$totalmoney['money'];
        $type_dict=[
            0=>'维保查询',
            1=>'碰撞查询',
            2=>'汽车状态查询',
            3=>'违章查询',
            4=>'小综合查询',
            5=>'大综合查询'
        ];
        $status_dict=[
            0=>'未付款',
            1=>'直接查询',
            2=>'付款查询',
            3=>'查询成功',
            4=>'部分成功',
            5=>'查询失败',
            5=>'用户放弃查询'
        ];
        foreach ($car_list['rs'] as $key=>$value){
            $car_list['rs'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $car_list['rs'][$key]['type_msg'] = $type_dict[$value['type']];
            $car_list['rs'][$key]['status_msg'] = $status_dict[$value['status']];
        }
        return ['code' => 200, 'data' => $car_list];
    }



}
