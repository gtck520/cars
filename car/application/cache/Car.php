<?php

namespace app\cache;

use king\lib\Cache;
use app\model\CarType as CarTypeModel;

class Car
{
    private static $key = 'car:name:list';
    private static $car_type_key = 'car:type:list';
    private static $car_bs_key = 'car:bs:list';
    //
    public static function getCarName(){
        if (!Cache::exists(self::$key)) {
            self::setCarName();
        }

        return Cache::get(self::$key);
    }

    public static function setCarName()
    {
        $cars = CarTypeModel::field(['distinct MAKE_NAME', 'FIRST_LETTER'])->get();
        Cache::set(self::$key, json_encode($cars));
    }

    public static function setCarType()
    {
        $car_type_list = CarTypeModel::field(['distinct VEHICLE_CLASS'])->get();
        Cache::set(self::$car_type_key, json_encode($car_type_list));
    }
    
    public static function getCarType(){
        if (!Cache::exists(self::$car_type_key)) {
            self::setCarType();
        }
        return Cache::get(self::$car_type_key);
    }

    public static function setCarBS()
    {
        $car_bs_list = CarTypeModel::field(['distinct TRANSMISSION'])->get();
        Cache::set(self::$car_bs_key, json_encode($car_bs_list));
    }
    
    public static function getCarBS(){
        if (!Cache::exists(self::$car_bs_key)) {
            self::setCarBS();
        }
        return Cache::get(self::$car_bs_key);
    }
}