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
    public static function register($user_id, $req)
    {
        if ($req['invite_mobile'] ==  $req['mobile']) {
            return ['code' => 400, 'data' => "推荐人不能是自己 !"];
        }
        $user_mobile = UserModel::where(['mobile' => $req['invite_mobile']])->find();
        if (!$user_mobile) {
            return ['code' => 400, 'data' => "{$req['invite_mobile']}  邀请人手机号码不存在!"];
        }
        $user_info = UserModel::where(['mobile' => $req['mobile']])->find();
        if ($user_info) {
            return ['code' => 400, 'data' => "{$user_info['mobile']} 已存在!"];
        }

        $city = CityModel::where(['id' => $req['area_id']])->find();
        if (!$city) {
            return ['code' => 400, 'data' => '无此城市!'];
        }

        if ($city['level'] != 3) {
            return ['code' => 400, 'data' => '城市级别错误!'];
        }

        $images_arr = explode('|', $req['images']);
        if (count($images_arr) > 2) {
            return ['code' => 400, 'data' => '最多只能上传两种图片!'];
        }
        UserModel::startTrans();
        try {
            //门店id
            $shop_info = ShopModel::where(['name' => $req['shop_name'], 'address' => $req['shop_address']])->find();
            if ($shop_info) {
                $shop_id = $shop_info['id'];
            } else {
                $shop_id = 0;
            }



            $city_id = explode(',', $city['path']);

            $data = [
                'mobile'          => $req['mobile'],
                'realname'        => $req['realname'],
                'city_fullname'   => $city['fullname'],
                'shop_id'         => $shop_id,
                'province_id'  => $city_id[0],
                'area_id'  => $city_id[2],
                'city_id'         => $city_id[1],
                'level_id'        => 1,
                'quan_guo'        => 0,
                'sheng_ji'        => 0,
                'images_url'      => $req['images'],
                'status'          => 0,
                'create_time'     => time(),
                'last_login_time' => time(),
            ];

            UserModel::where(['id' => $user_id])->update($data);
            UserModel::endTrans();

            return ['code' => 200, 'data' => ''];
        } catch (\Exception $e) {
            UserModel::rollback();
            return ['success' => false, 'data' => '事务操作中断！' . $e->getMessage()];
        }
    }

    //当前用户信息
    public static function userInfo($user_id)
    {
        $field = ['id', 'mobile', 'nickname', 'avatar', 'realname', 'city_fullname', 'openid', 'quan_guo', 'sheng_ji', 'level_id', 'status', 'images_url', 'money', 'create_time', 'last_login_time', 'shop_id'];
        $user_info = UserModel::field($field)->where(['id' => $user_id])->find();
        if ($user_info) {
            $user_info['shop'] = ShopModel::field(['id', 'name', 'address'])->where(['id' => $user_info['shop_id']])->find();
            $user_info = Helper::formatTimt($user_info, ['create_time', 'last_login_time']);
            // $user_info['mobile'] =  substr_replace($user_info['mobile'], '****', 3, 4);
            $user_info['images_url'] = explode('|', $user_info['images_url']);
            unset($user_info['shop_id']);
        }

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
    public static function addPrice($user_id, $req)
    {
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

    //发布的车 我的车源  
    public static function Cars($user_id, $status)
    {
        $field = ['a.id', 'a.area_id', 'a.price', 'a.chexing_id', 'shangpai_time', 'a.biaoxianlicheng', 'a.create_time', 'a.liulan_num', 'a.images_url', 'a.phone_num', 'a.bangmai_num', 'b.MODEL_NAME', 'b.TYPE_SERIES', 'b.TYPE_NAME'];
        $car_list = CarModel::setTable('car a')->field($field)->join('car_type b', 'a.chexing_id = b.ID')->where('a.user_id', '=', $user_id)->where('a.status', '=', $status)->get();
        if ($car_list) {
            foreach ($car_list as &$value) {
                //格式化返回
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                $value['image'] = explode('|', $value['images_url']) ? explode('|', $value['images_url'])[0] : [];
                $value['price_num'] = CarPriceModel::where(['user_id' => $user_id])->count();
                unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME'], $value['images_url']);
            }
        }

        return ['code' => 200, 'data' => $car_list];
    }

    //添加电话量
    public static function addPhoneNum($user_id, $car_id)
    {
        CarModel::where(['id' => $car_id])->update(['phone_num +' => 1]);
        return ['code' => 200, 'data' => ''];
    }

    //获取出价记录
    public static function getPrice($user_id)
    {
        $orderby = ['a.create_time' => 'desc'];

        $field = ['a.price', 'a.create_time', 'b.id', 'b.price', 'b.chexing_id', 'b.biaoxianlicheng', 'b.shangpai_time', 'b.area_id', 'b.images_url', 'b.status', 'c.MODEL_NAME', 'c.TYPE_SERIES', 'c.TYPE_NAME'];

        $car_list = CarModel::setTable('car_price a')->join('car b', 'a.car_id = b.id')->join('car_type c', 'b.chexing_id = c.ID')->field($field)->where('a.user_id', '=', $user_id)->orderby($orderby)->get();

        if ($car_list) {
            foreach ($car_list as &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['image'] = explode('|', $value['images_url']) ? explode('|', $value['images_url'])[0] : [];
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME'], $value['images_url']);
            }
        }

        return ['code' => 200, 'data' => $car_list];
    }


    //其他用户信息
    public static function getUserInfo($user_id)
    {
        $field = ['id', 'mobile', 'nickname', 'avatar', 'realname', 'city_fullname', 'create_time', 'last_login_time', 'shop_id'];
        //已审核用户
        $user_info = UserModel::field($field)->where(['id' => $user_id, 'status' => 1])->find();
        if ($user_info) {
            $user_info['shop'] = ShopModel::field(['id', 'name', 'address'])->where(['id' => $user_info['shop_id']])->find();
            $user_info = Helper::formatTimt($user_info, ['create_time', 'last_login_time']);
            // $user_info['mobile'] =  substr_replace($user_info['mobile'], '****', 3, 4);
            unset($user_info['shop_id'], $user_info['openid']);
        }

        return ['code' => 200, 'data' => $user_info];
    }
}
