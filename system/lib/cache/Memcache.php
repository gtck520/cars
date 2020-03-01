<?php

namespace king\lib\cache;
class Memcache
{
    private static $instance;
    private $memcache;
    private $compress = false;//是否压缩数据

    public function __construct()
    {
        $config = C('cache.*');
        if (!empty($config['memcache']))//如果有memcache
        {
            if (!is_object($this->memcache)) {
                $this->memcache = new \Memcache;
                $servers = $config['memcache'];
                foreach ($servers as $server) {
                    $persist = isset($server['persist']) ? $server['persist'] : false;
                    $this->memcache->addserver($server['host'], $server['port'], $persist);
                }
            }
        } else {
            return 'memcache config is empty';
        }
    }

    public function get($id)
    {
        return $this->memcache->get($id);
    }

    public function delete($id)//删除cache id
    {
        return $this->parentDelete($id);
    }

    public function deleteAll()//删除所有缓存
    {
        return $this->parentDelete(TRUE);
    }

    public function setCompress()
    {
        $this->compress = true;
    }

    public function getCacheInfo()//取得memcache的统计信息
    {
        return $this->memcache->getStats();
    }

    private function parentDelete($id)//id=TRUE时更新所有缓存
    {
        if ($id === TRUE)//全部清空时
        {
            if ($status = $this->memcache->flush()) {
                sleep(1);
            }
            return $status;
        } else//删除某个id
        {
            return $this->memcache->delete($id);

        }
    }

    public function set($key, $data, $time = 3600)
    {
        return $this->memcache->set($key, $data, $this->compress, $time);
    }
}	