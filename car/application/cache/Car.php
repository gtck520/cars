<?php

namespace app\cache;

use king\lib\Cache;
use app\model\City as CityModel;
use app\model\CarType as CarTypeModel;
use app\service\Car as CarService;

class Car  extends Cache
{
    private static $key = 'car:name:list';
    private static $car_type_key = 'car:type:list';
    private static $car_bs_key = 'car:bs:list';
    private static $car_pl_key = 'car:pl:list';
    private static $car_cllx_key = 'car:cllx:list';
    private static $city_list_ley = 'city:list';
    private static $is_open_key = 'is:open';
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
        $car_info = CarService::CarInfo($car_id);
        return Cache::hMset(self::$car_inro_key . $car_id, $car_info);
    }

    //品牌缓存
    public static function getCarName()
    {
        if (!Cache::exists(self::$key)) {
            self::setCarName();
        }

        return Cache::get(self::$key);
    }

    //品牌缓存
    public static function setCarName()
    {
        $cars = CarTypeModel::field(['distinct MAKE_NAME', 'FIRST_LETTER'])->get();
        Cache::set(self::$key, json_encode($cars));
    }

    //车型缓存
    public static function setCarType()
    {
        $car_type_list = CarTypeModel::field(['distinct VEHICLE_CLASS'])->get();
        Cache::set(self::$car_type_key, json_encode($car_type_list));
    }

    //车型缓存
    public static function getCarType()
    {
        if (!Cache::exists(self::$car_type_key)) {
            self::setCarType();
        }
        return Cache::get(self::$car_type_key);
    }

    //变速缓存
    public static function setCarBS()
    {
        $car_bs_list = CarTypeModel::field(['distinct TRANSMISSION'])->get();
        Cache::set(self::$car_bs_key, json_encode($car_bs_list));
    }

    //变速缓存
    public static function getCarBS()
    {
        if (!Cache::exists(self::$car_bs_key)) {
            self::setCarBS();
        }

        return Cache::get(self::$car_bs_key);
    }

    //排量缓存
    public static function setCarPL()
    {
        $car_pl_list = CarTypeModel::field(['distinct ENGINE_CAPACITY'])->get();
        Cache::set(self::$car_pl_key, json_encode($car_pl_list));
    }


    //排量缓存
    public static function getCarPL()
    {
        if (!Cache::exists(self::$car_pl_key)) {
            self::setCarPL();
        }

        return Cache::get(self::$car_pl_key);
    }

    //车辆类型
    public static function setCarCLLX()
    {
        $car_cllx_list = CarTypeModel::field(['distinct MODEL_NAME'])->get();
        Cache::set(self::$car_cllx_key, json_encode($car_cllx_list));
    }


    //车辆类型
    public static function getCarCLLX()
    {
        if (!Cache::exists(self::$car_cllx_key)) {
            self::setCarCLLX();
        }

        return Cache::get(self::$car_cllx_key);
    }

    //城市列表缓存
    public static function getCityListCache()
    {
        if (!Cache::exists(self::$city_list_ley)) {
            self::setCityListCache();
        }

        return Cache::get(self::$city_list_ley);
    }

    //设置城市列表缓存
    public static function setCityListCache()
    {
        $filed = ['id', 'name'];
        $data['province_list'] = array_column(CityModel::field($filed)->where(['level' => 1])->get(), 'name', 'id');
        $data['city_list'] = array_column(CityModel::field($filed)->where(['level' => 2])->get(), 'name', 'id');
        $data['county_list'] = array_column(CityModel::field($filed)->where(['level' => 3])->get(), 'name', 'id');
        return Cache::set(self::$city_list_ley, json_encode($data));
    }

    //是否开启城市查询权限
    public static function getIsOpen()
    {
        if (!Cache::exists(self::$is_open_key)) {
            Cache::set(self::$is_open_key, '0');
        }

        return Cache::get(self::$is_open_key);
    }
}
