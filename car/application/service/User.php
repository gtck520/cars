<?php

namespace app\service;

use app\cache\Token;
use app\helper\Helper;
use app\model\PayRecords as  PayRecordsModel;
use app\service\Pay as PayService;
use app\model\CarPrice as CarPriceModel;
use app\model\Car as CarModel;
use app\model\CarSc as CarScModel;
use app\model\City as CityModel;
use app\model\User as UserModel;
use app\model\Shop as ShopModel;
use app\model\CarBrowse as CarBrowseModel;

class User
{
    public static function register($req)
    {
        $user_mobile = UserModel::where(['mobile' => $req['invite']])->find();
        if (!$user_mobile) {
            return ['code' => 400, 'data' => "{$req['invite']}  邀请人手机号码不存在!"];
        }
        $user_info = UserModel::where(['mobile' => $req['mobile']])->find();
        if ($user_info) {
            return ['code' => 400, 'data' => "{$user_info['mobile']} 已存在!"];
        }
        UserModel::startTrans();
        try {
            $shop_id = ShopModel::insert([
                'name'        => $req['shop_name'],
                'address'     => $req['shop_address'],
                'create_time' => time(),
            ]);
            $city_fullname = CityModel::where(['id' => $req['city_id']])->find();
            if (!$city_fullname) {
                return ['code' => 400, 'data' => '无此地区!'];
            }

            $data = [
                'mobile'          => $req['mobile'],
                'nickname'        => $req['nickname'],
                'avatar'          => $req['avatar'],
                'realname'        => $req['realname'],
                'city_fullname'   => $city_fullname,
                'shop_id'         => $shop_id,
                'city_id'         => $req['city_id'],
                'level_id'        => $req['level_id'],
                'openid'          => $req['openid'],
                'quan_guo'        => 0,
                'sheng_ji'        => 0,
                'images_url'      => $req['images_url'],
                'status'          => 0,
                'create_time'     => time(),
                'last_login_time' => time(),
            ];

            UserModel::insert($data);
            UserModel::endTrans();

            return ['code' => 200, 'data' => ''];
        } catch (\Exception $e) {
            UserModel::rollback();
            return ['success' => false, 'data' => '事务操作中断！' . $e->getMessage()];
        }
    }

    public static function userInfo($user_id)
    {
        $user_info = UserModel::where(['id' => $user_id])->find();
        $user_info['shop'] = ShopModel::where(['id' => $user_info['shop_id']])->find();
        $user_info = Helper::formatTimt($user_info, ['create_time', 'last_login_time']);
        $user_info['mobile'] =  substr_replace($user_info['mobile'], '****', 3, 4);
        unset($user_info['shop_id']);
        return ['code' => 200, 'data' => $user_info];
    }

    //删除收藏
    public static function enshrineDel($user_id, $car_id)
    {
        CarScModel::delete(['user_id' => $user_id, 'car_id' => $car_id]);
        return ['code' => 200, 'data' => ''];
    }

    //删除浏览
    public static function browseDel($user_id, $car_id)
    {
        CarBrowseModel::delete(['user_id' => $user_id, 'car_id' => $car_id]);
        return ['code' => 200, 'data' => ''];
    }

    //车辆出价  
    public static function addPrice($user_id, $req){
        if (!isset($req['car_id'])) {
            return ['code' => 400, 'data' => '车辆id没有定义'];
        }

        if (!isset($req['price']) || !is_numeric($req['price'])) {
            return ['code' => 400, 'data' => '价格没有定义或者非法'];
        }
        CarPriceModel::insert([
            'user_id' => $user_id,
            'price'  => $req['price'],
            'car_id' => $req['car_id'],
            'create_time' => time(),
        ]);

        return ['code' => 200, 'data' => ''];
    }

    // 用户充值
    public static function recharge($user_id, $param)
    {   
        $pay_type = $param['pay_type'];
        $type = $param['type'];
        $method = $param['method'];
        $amount = sprintf($param['amount'], 2);
        //生成订单号
        $trade_no = Helper::getMicrotime();

        // 数据库添加待支付订单
        $rs = PayRecordsModel::insert([
            'trade_no'     => $trade_no,
            'user_id'      => $user_id,
            'type'         => $type,
            'pay_type'     => $pay_type,
            'pay_amount'   => $amount,
            'status'       => 0,
            'pay_trade_no' => '',
            'paid_time'    => 0,
            'created_time' => time(),
            'updated_time' => time(),
        ]);
        if ($rs) {
            PayService::pay($trade_no, $amount, $pay_type, $method);
        }
    }
}
