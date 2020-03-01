<?php

namespace king\lib;

/**
 *
 * @param string $type 目前有三种类型lite,memcache,redis
 */

class Cache
{
    protected static $instances = [];

    public static function getClass($cache_type = '')
    {
        $cache_type = $cache_type ?: C('cache_driver');
        if (!isset(Cache::$instances[$cache_type])) {
            $class = 'king\lib\cache\\' . ucfirst($cache_type);
            Cache::$instances[$cache_type] = new $class;
        }

        return Cache::$instances[$cache_type];
    }

    public static function __callStatic($method, $params)
    {
        return call_user_func_array([self::getClass(), $method], $params);
    }

}	