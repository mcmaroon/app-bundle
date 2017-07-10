<?php

namespace App\AppBundle\Cache;

use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;

class FixCachePool extends AbstractCachePool {

    protected function fetchObjectFromCache($key) {

    }

    protected function clearAllObjectsFromCache() {

    }

    protected function clearOneObjectFromCache($key) {

    }

    protected function storeItemInCache(CacheItemInterface $item, $ttl) {

    }

}
