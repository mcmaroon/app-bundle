<?php

namespace App\AppBundle\Helper;

class CacheTxt {

    CONST DEFAULT_CACHE_FILE_NAME = 'default';

    private $cacheFilePath;

    /**
     * @param type $path
     * @param type $cacheFileName
     */
    public final function __construct($path = '', $cacheFileName = self::DEFAULT_CACHE_FILE_NAME) {
        $this->cacheFilePath = $path . '/cache.' . $cacheFileName . '.txt';
    }

    // ~

    public final function getCacheFilePath() {
        return $this->cacheFilePath;
    }

    // ~

    public final function removeCacheFile() {
        if (\file_exists($this->cacheFilePath)) {
            \unlink($this->cacheFilePath);
        }
    }

    // ~

    private final function loadCache() {
        try {
            return \unserialize(\file_get_contents($this->cacheFilePath));
        } catch (\Exception $ex) {
            
        }
        return array();
    }

    /**
     *
     * @param type $key
     * @param type $data
     */
    public final function setCache($key, $data) {
        $newData = $this->loadCache();
        $newData[$key] = $data;
        \file_put_contents($this->cacheFilePath, \serialize($newData));
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public final function getCache($key) {
        $cache = $this->loadCache();
        return isset($cache[$key]) ? $cache[$key] : null;
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public final function removeCache($key) {
        $cache = $this->loadCache();
        if (isset($cache[$key])) {
            unset($cache[$key]);
            \file_put_contents($this->cacheFilePath, \serialize($cache));
            return true;
        }
        return false;
    }

}
