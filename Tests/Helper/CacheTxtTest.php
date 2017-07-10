<?php

namespace App\AppBundle\Tests\Helper\Traits;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\AppBundle\Helper\CacheTxt;

class CacheTxtTest extends KernelTestCase {

    protected function setUp() {
        self::bootKernel();
    }

    public function testStringCache() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->setCache('sample:cache:string', 'sample string');
        $cache = $ct->getCache('sample:cache:string');
        $this->assertEquals($cache, 'sample string');
    }

    // ~

    public function testStringCacheOverride() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->setCache('sample:cache:string', 'sample string override');
        $cache = $ct->getCache('sample:cache:string');
        $this->assertEquals($cache, 'sample string override');
    }

    // ~

    public function testStringCacheRemove() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->removeCache('sample:cache:string');
        $cache = $ct->getCache('sample:cache:string');
        $this->assertNull($cache);
    }

    // ~

    public function testArrayCache() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->setCache('sample:cache:array', ['a' => 1, 'b' => 2]);
        $cache = $ct->getCache('sample:cache:array');
        $this->assertEquals($cache, ['a' => 1, 'b' => 2]);
    }

    // ~

    public function testArrayCacheOverride() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->setCache('sample:cache:array', ['a' => 3, 'b' => 4]);
        $cache = $ct->getCache('sample:cache:array');
        $this->assertEquals($cache, ['a' => 3, 'b' => 4]);
    }

    // ~

    public function testArrayCacheRemove() {
        $ct = new CacheTxt(static::$kernel->getLogDir());
        $ct->removeCache('sample:cache:array');
        $cache = $ct->getCache('sample:cache:array');
        $this->assertNull($cache);
    }

    // ~

    public function testCustomCacheFilePath() {
        $ct = new CacheTxt(static::$kernel->getLogDir(), 'test');
        $ct->setCache('custom:cache:string', 'custom string');
        $cache = $ct->getCache('custom:cache:string');
        $this->assertEquals($cache, 'custom string');
    }

    // ~

    public function testRemoveCustomTestCacheFiles() {
        $ct = new CacheTxt(static::$kernel->getLogDir(), 'test');
        $this->assertTrue(\file_exists($ct->getCacheFilePath()));
        $ct->removeCacheFile();
        $this->assertFalse(\file_exists($ct->getCacheFilePath()));
    }

}
