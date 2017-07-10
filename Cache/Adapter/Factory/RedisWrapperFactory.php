<?php

namespace App\AppBundle\Cache\Adapter\Factory;

use Cache\Adapter\Redis\RedisCachePool;
use Redis;
use App\AppBundle\Cache\FixCachePool;

class RedisWrapperFactory extends \Cache\AdapterBundle\Factory\RedisFactory {

    public function getAdapter(array $config) {
        if (class_exists('Redis')) {
            try {
                $client = new Redis();

                $dsn = $this->getDsn();
                if (empty($dsn)) {
                    $client->connect($config['host'], $config['port']);
                } else {
                    if (!empty($dsn->getPassword())) {
                        $client->auth($dsn->getPassword());
                    }

                    $client->connect($dsn->getFirstHost(), $dsn->getFirstPort());
                }

                return new RedisCachePool($client);
            } catch (\Exception $exc) {

            }
        }
        return new FixCachePool();
    }

}
