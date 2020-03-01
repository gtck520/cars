<?php

namespace app\service;

use app\helper\Helper;
use king\lib\Jwt;
use app\cache\Car as CarCache;
use app\model\Car as CarModel;
use app\model\City as CityModel;
use app\model\User as UserModel;
use app\model\CarType as CarTypeModel;
use app\model\Impeach as ImpeachModel;
use app\model\CarColour as CarColourModel;

class Car
{
    private static $impeach_type = [
        '1' => '非真实车源',
        '2' => '车源已售',
        '3' => '车源描述信息有误',
        '4' => '其他',
    ];
    public static function modify($car_id, $new = [])
    {
        CarModel::where(['car_id' => $car_id])->update($new);
        CarCache::update($car_id, $new);
    }

    public static function getList($user_id, $req)
    {
        $user_info = UserModel::field(['city_id', 'shop_id'])->where(['user_id' => $user_id])->find();
        dd($user_info);
        $orderby = ['a.create_time' => 'desc', 'a.id' => 'asc'];

        $field = ['a.id', 'a.cheliangweizhi', 'a.price', 'a.chexing_id', 'shangpai_time', 'a.biaoxianlicheng', 'a.create_time', 'b.MODEL_NAME', 'b.TYPE_SERIES', 'b.TYPE_NAME'];

        // 关键字搜索
        $query = CarModel::setTable('car a')->field($field)->join('car_type b', 'a.chexing_id = b.ID');
        if (isset($req['search']) || !empty($req['search'])) {
            $search = $req['search'];
            $query->andWhere(function ($query) use ($search) {
                $query->where('b.MODEL_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.MODEL_NAME', 'like', '%' . $search . '%');
                $query->whereOr('b.TYPE_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.VEHICLE_CLASS', 'like', '%' . $search . '%');
                $query->whereOr('b.TRANSMISSION', 'like', '%' . $search . '%');
            });
        }

        // 关键字搜索
        if (!empty($req['city_id']) || isset($req['city_id'])) {
            $query->where('a.cheliangweizhi', '=', $req['city_id']);
        }

        // 品牌搜索
        if (!empty($req['pinpai']) || isset($req['pinpai'])) {
            $query->where('a.pinpai', '=', $req['pinpai']);
        }

        // 最小价格区间查询
        if (!empty($req['low_price']) || isset($req['low_price'])) {
            $query->where('a.price', '<=', $req['low_price']);
        }

        // 最大价格区间查询
        if (!empty($req['high_price']) || isset($req['high_price'])) {
            $query->where('a.price', '>=', $req['high_price']);
        }


        // 颜色搜索
        if (!empty($req['colour_id']) || isset($req['colour_id'])) {
            $query->where('a.colour_id', '=', $req['colour_id']);
        }

        // 车辆类型搜索
        if (!empty($req['car_type']) || isset($req['car_type'])) {
            $query->where('a.car_type', '=', $req['car_type']);
        }

        // 变速箱搜索
        if (!empty($req['biansu']) || isset($req['biansu'])) {
            $query->where('a.biansu', '=', $req['biansu']);
        }

        // 车辆标签搜索
        if (!empty($req['cheyuan_id']) || isset($req['cheyuan_id'])) {
            $query->where('a.cheyuan_id', '=', $req['cheyuan_id']);
        }

        // 车辆里程搜索
        if (!empty($req['licheng']) || isset($req['licheng'])) {
            $query->where('a.biaoxianlicheng', '=', $req['licheng']);
        }

        // 最小车龄区间查询
        if (!empty($req['low_age']) || isset($req['low_age'])) {
            $query->where('a.age', '<=', $req['low_age']);
        }

        // 最大车龄区间查询
        if (!empty($req['high_age']) || isset($req['high_age'])) {
            $query->where('a.age', '>=', $req['high_age']);
        }

        // 排序
        if (!empty($req['sort']) || isset($req['sort'])) {
            switch ($req['sort']) {
                case '1':
                    // 价格最低
                    $orderby = ['a.price' => 'asc', 'a.id' => 'asc'];
                    break;
                case '2':
                    // 价格最高
                    $orderby = ['a.price' => 'desc', 'a.id' => 'asc'];
                    break;
                case '3':
                    // 最新发布
                    $orderby = ['a.create_time' => 'desc', 'a.id' => 'asc'];
                    break;
            }
        }

        $car_list = $query->orderby($orderby)->page($req['c'], $req['p']);
        if ($car_list['total'] > 0) {
            foreach ($car_list['rs'] as &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['cheliangweizhi']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['cheliangweizhi'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
            }
        }

        return ['code' => 200, 'data' => $car_list];
    }

    // 
    public static function getCarName()
    {
        $car_name = CarCache::get();
        return ['code' => 200, 'data' => $car_name];
    }

    //更新缓存
    public static function setCache()
    {
        $cars = CarTypeModel::field(['distinct MAKE_NAME', 'FIRST_LETTER'])->get();
        CarCache::set($cars);
        return ['code' => 200, 'data' => '更新成功!'];
    }

    //车辆详情
    public static function getCarInfo($car_id)
    {
        $car_info = CarModel::where(['id' => $car_id, 'status' => 1])->find();
        $car_info = Helper::formatTimt($car_info, ['shangpai_time', 'nianjiandaoqi', 'qiangxiandaoqi', 'create_time']);
        $car_info['yanse'] = CarColourModel::where(['id' => $car_info['yanse_id']])->value('name');
        $car_info['biaoxianlicheng'] = $car_info['biaoxianlicheng'] . '万公里';
        $car_info['city_name'] = CityModel::where(['id' => $car_info['cheliangweizhi']])->value(['fullname']);
        $car_info['realname'] = UserModel::where(['id' => $car_info['user_id']])->value(['realname']);
        $car_info['realname'] = Helper::encryptName($car_info['realname']);
        $car_info['chexing'] = CarTypeModel::field(['MODEL_NAME', 'ENGINE_CAPACITY'])->where(['ID' => $car_info['cheixng_id']])->find();
        unset($car_info['yanse_id'], $car_info['status'], $car_info['cheixng_id'], $car_info['id']);

        return ['code' => 200, 'data' => $car_info];
    }

    //举报
    public static function impeach($user_id, $car_id, $type_id)
    {
        $res = ImpeachModel::where(['user_id' => $user_id, 'car_id' => $car_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => '您已经投诉过该车辆, 正在处理中!'];
        } else {
            ImpeachModel::insert([
                'user_id' => $user_id,
                'car_id' => $car_id,
                'notes' => self::$impeach_type[$type_id],
                'create_time' => time(),
            ]);

            return ['code' => 200, 'data' => ''];
        }
    }
}
