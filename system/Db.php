<?php

namespace king;

use king\core\Db as Instance;
use king\core\Loader;

class Db
{
    public static $instance = [];
    public static $debug = false;

    public static function connect()
    {
        $db_set = static::$db_set ?? 'default';
        if (!isset(self::$instance[$db_set])) {
            self::$instance[$db_set] = new Instance($db_set);
        }
        
        $table = static::$table ?? self::getClassName(static::class);
        self::$instance[$db_set]->setTable($table);
        self::$instance[$db_set]->setDebug(self::$debug);
        $key = static::$key ?? 'id';
        self::$instance[$db_set]->setKey($key);
        return self::$instance[$db_set];
    }

    private static function getClassName($class)
    {
        $array = explode('\\', $class);
        return strtolower(end($array));
    }

    public static function setDebug($debug = 'echo')
    {
        static::$debug = $debug;
    }

    public static function __callStatic($method, $params)
    {
        return call_user_func_array([self::connect(), $method], $params);
    }
}