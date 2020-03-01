<?php

namespace app\cache;

use king\lib\Cache;

class Token
{
    public static $expire = 7200;
    private static $tokenKey = 'mamagerToken';
    
    //
    public static function set($user_id, $exp){
        $key = md5(self::$tokenKey.$user_id.time());
        Cache::setex($key, $exp, $user_id);
        return $key;
    }

    //
    public static function get($key){
        return Cache::get($key);
    }

    //
    public static function keep($key){
        Cache::expire($key, self::$expire);
    }
}