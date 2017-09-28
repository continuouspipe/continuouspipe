<?php

namespace River;

use Behat\Behat\Context\Context;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FlushableCache;
use Predis\ClientInterface;

class CacheContext implements Context
{
    /**
     * @var FlushableCache
     */
    private $cache;

    /**
     * @param FlushableCache $cache
     */
    public function __construct(FlushableCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Given the cache is clean
     */
    public function theCacheIsClean()
    {
        $this->cache->flushAll();
    }
}
