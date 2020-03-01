<?php

namespace app\cache;

use king\lib\Cache;
use app\model\CarType as CarTypeModel;

class Car
{
    private static $key = 'car:name:list';
    
    //
    public static function get(){
        if (!Cache::exists(self::$key)) {
            $cars = CarTypeModel::field(['distinct MAKE_NAME', 'FIRST_LETTER'])->get();
            if($cars){
                self::set($cars);
            }
        }
        return Cache::get(self::$key);
    }

    public static function set($car_list)
    {
        Cache::set(self::$key, json_encode($car_list));
    }

    public static function update($car_id)
    {
        
    }

}