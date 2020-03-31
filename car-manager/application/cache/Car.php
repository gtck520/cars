<?php

namespace app\cache;

use king\lib\Cache;
use app\model\Car as CarModel;
use app\model\CarType as CarTypeModel;
use app\helper\Helper;
use app\model\CarColour as CarColourModel;
use app\model\CityArea as CityAreaModel;
use app\model\User as UserModel;

class Car extends Cache
{
    private static $car_inro_key = 'car:info:';
    
    //获取车辆详情
    public static function getCarInfo($car_id)
    {
        if (!Cache::exists(self::$car_inro_key . $car_id)) {
            self::setCarInfo($car_id);
        }

        return Cache::hGetAll(self::$car_inro_key . $car_id);
    }

    //设置车辆详情
    public static function setCarInfo($car_id)
    {
        $car_info = self::CarInfo($car_id);
        return Cache::hMset(self::$car_inro_key . $car_id, $car_info);
    }

    //车辆详情
    public static function CarInfo($car_id)
    {
        $field = ['user_id', 'chejiahao', 'pinpai', 'chexing_id', 'shangpai_time', 'area_id', 'price', 'biaoxianlicheng', 'nianjiandaoqi', 'qiangxiandaoqi', 'weixiujilu', 'pengzhuangjilu', 'notes', 'images_url', 'status', 'is_hidden', 'create_time', 'biansu', 'zhengming', 'yanse_id', 'pl', 'update_time'];
        $car_info = CarModel::field($field)->where(['id' => $car_id])->find();
        $car_info = Helper::formatTimt($car_info, ['shangpai_time', 'nianjiandaoqi', 'qiangxiandaoqi'], 'Y-m-d');
        $car_info['yanse'] = CarColourModel::where(['id' => $car_info['yanse_id']])->value('name');
        $car_info['biaoxianlicheng'] = $car_info['biaoxianlicheng'];
        $car_info['city_name'] = CityAreaModel::where(['id' => $car_info['area_id']])->value(['fullname']);
        $car_info['realname'] = UserModel::where(['id' => $car_info['user_id']])->value(['realname']);
        $car_info['realname'] = Helper::encryptName($car_info['realname']);
        $chexing = CarTypeModel::field(['MODEL_NAME', 'TYPE_SERIES', 'TECHNOLOGY', 'VEHICLE_CLASS', 'TRANSMISSION'])->where(['ID' => $car_info['chexing_id']])->find();
        $car_info['title'] = "{$chexing['MODEL_NAME']} {$chexing['TYPE_SERIES']} {$car_info['pl']} {$chexing['TECHNOLOGY']} {$chexing['VEHICLE_CLASS']} {$chexing['TRANSMISSION']}";
        $car_info['car_type'] = $chexing['TYPE_SERIES'];
        $car_info['pailiang'] = $car_info['pl'];
        $car_info['cheliangleixing'] = $chexing['VEHICLE_CLASS'];
        $car_info['user_mobile'] = UserModel::where(['id' => $car_info['user_id']])->value('mobile');
        unset($car_info['yanse_id'], $car_info['cheixng_id'], $car_info['id'], $car_info['chexing_id']);
        return $car_info;
    }
}