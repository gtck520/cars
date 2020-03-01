<?php

namespace king\lib\cache;
class Lite
{
    private static $instance;
    private $lite;

    public function __construct()
    {
        $default_config = ['life_time' => 7200, 'level' => 2, 'cache_dir' => 'lite'];
        $config = C('cache.*');
        if (!empty($config['lite'])) {
            $lite_config = $config['lite'];
            foreach ($lite_config as $key => $value) {
                if (isset($default_config[$key])) {
                    $default_config[$key] = $value;
                }
            }
        }

        $option = [
            'cacheDir' => APP_PATH . $default_config['cache_dir'] . DS,
            'lifeTime' => $default_config['life_time'],
            'hashedDirectoryLevel' => $default_config['level'],
            'automaticSerialization' => true
        ];


        if (!is_object($this->lite)) {
            $obj = require_once 'Cache/Lite.php';
            $this->lite = new \Cache_Lite($option);
        }
    }

    public function get($key)
    {
        return $this->lite->get($key);
    }

    public function delete($key) // 删除cache key
    {
        return $this->lite->remove($key);
    }

    public function set($data, $key = '') // key未传递时使用get的key值
    {
        if ($key) {
            return $this->lite->save($data, $key);
        } else {
            return $this->lite->save($data);
        }
    }
}	