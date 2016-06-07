<?php

namespace dix\base\component;

use Yii;

class Redis
{
    /**
     * @return null|\Predis\Client
     */
    public static function client()
    {
        static $redis = null;

        if ($redis === null)
        {
            $redis = new \Predis\Client(DXUtil::param('redis-param'), DXUtil::param('redis-option'));
        }

        return $redis;
    }

    /**
     * @return null|\Predis\Client
     */
    public static function createClient()
    {
        return new \Predis\Client(DXUtil::param('redis-param'), DXUtil::param('redis-option'));
    }

    /**
     * @return null|\Predis\Client
     */
    public static function clientWithNoPrefix()
    {
        static $redis = null;

        if ($redis === null)
        {
            $options = DXUtil::param('redis-option');
            unset($options['prefix']);
            $redis = new \Predis\Client(DXUtil::param('redis-param'), $options);
        }

        return $redis;
    }





    /**
     * lock based on redis incr
     */

    private static function keyOfLock($key, $id)
    {
        return "redis.lock.$key.$id";
    }

    public static function lock($key, $id, $expire_seconds = 60)
    {
        $key = self::keyOfLock($key, $id);
        $redis = self::client();
        $count = $redis->incr($key);
        $redis->expire($key, $expire_seconds);
        return $count == 1;
    }

    public static function unlock($key, $id)
    {
        $key = self::keyOfLock($key, $id);
        $redis = self::client();
        $redis->del($key);
    }
}