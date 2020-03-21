<?php

namespace app\service;

use app\helper\Helper;
use app\model\User as UserModel;
use app\service\CityActive as CityActiveService;
use app\model\CityArea as CityAreaModel;

class User
{
    //列表
    public static function getList($req)
    {
        $orderby = ['last_login_time' => 'desc'];
        $query = UserModel::setTable('user');
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
        //发布人人号码
        if (!empty($req['mobile'])) {
            $query->andWhere(function ($query) use ($req) {
                $query->where('mobile', 'like', '%' . $req['mobile'] . '%');
                $query->whereOr('realname', 'like', '%' . $req['mobile'] . '%');
            });
        }
        $res = $query->orderby($orderby)->page($req['c'], $req['p']);
        return ['code' => 200, 'data' => Helper::formatTimt($res, ['create_time', 'last_login_time'])];
    }

    //用户详情
    public static function getUserInfo($user_id)
    {
        $res = UserModel::where(['id' => $user_id])->find();
        return ['code' => 200, 'data' => Helper::formatTimt($res, ['create_time', 'last_login_time'])];
    }

    //修改
    public static function modify($admin_id, $user_id, $req)
    {
        $current_id=CityActiveService::getCurrentCity($req);//获取当前城市id
        $city_fullname = CityAreaModel::where(['id' => $current_id])->value(['fullname']);
     $updata=[
         'area_id'=>$req['area_id'],
        'city_id'=>$req['city_id'],
        'level_id'=>$req['level_id'],
        'mobile'=>$req['mobile'],
        'nickname'=>$req['nickname'],
        'province_id'=>$req['province_id'],
        'quan_guo'=>$req['quan_guo'],
        'realname'=>$req['realname'],
        'sheng_ji'=>$req['sheng_ji'],
        'shop_id'=>$req['shop_id'],
         'city_fullname'=>$city_fullname
     ];

        $res = UserModel::where(['mobile' => $req['mobile'],  'id <>' => $user_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "该电话号码已存在！"];
        }
        //
        $old_value = UserModel::where(['id' => $user_id])->find();

        UserModel::where(['id' => $user_id])->update($updata);
        //
        Helper::saveToLog(
            $admin_id,
            '',
            json_encode($old_value),
            json_encode($updata),
            "修改用户:$user_id"
        );
        return ['code' => 200, 'data' => ''];
    }
    //修改状态
    public static function modifyStatus($admin_id,$id, $req)
    {
        $where=[];
        if (isset($req['enabled'])) {
            $where['enabled']=$req['enabled'];
        }
        if (isset($req['status'])) {
            $where['status']=$req['status'];
        }
        if(empty($where)){
            return ['code' => 400, 'data' => "未做任何修改"];
        }
        //
        $old_value = UserModel::field(["enabled","status"])->where(['id' => $id])->find();
        UserModel::where(['id' => $id])->update($where);
        //
        $where['status']=$where['status'] ?? '未修改';
        $where['enabled']=$where['enabled'] ?? '未修改';
        Helper::saveToLog($admin_id, '',"下架状态：".$old_value['enabled']."审核状态：".$old_value['status'], "启用状态：".$where['enabled']."审核状态：".$where['status'], "管理员ID:$admin_id 修改用户状态ID:$id [启用状态：".$where['enabled']."审核状态".$where['status']."]");
        return ['code' => 200, 'data' => ''];
    }
}
