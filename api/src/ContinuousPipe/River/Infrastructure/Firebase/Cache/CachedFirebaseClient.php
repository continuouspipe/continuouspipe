<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Cache;

use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use Doctrine\Common\Cache\Cache;

class CachedFirebaseClient implements FirebaseClient
{
    /**
     * @var FirebaseClient
     */
    private $decoratedClient;

    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var int
     */
    private $lifeTime;

    public function __construct(FirebaseClient $decoratedClient, Cache $cache, int $lifeTime = 300)
    {
        $this->decoratedClient = $decoratedClient;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $databaseUri, string $path, array $value)
    {
        $cacheKey = $this->getCacheKey($databaseUri, $path, $value);

        if (!$this->cache->contains($cacheKey)) {
            $this->decoratedClient->set($databaseUri, $path, $value);

            $this->cache->save($cacheKey, $cacheKey, $this->lifeTime);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $databaseUri, string $path, array $value)
    {
        $cacheKey = $this->getCacheKey($databaseUri, $path, $value);

        if (!$this->cache->contains($cacheKey)) {
            $this->decoratedClient->update($databaseUri, $path, $value);

            $this->cache->save($cacheKey, $cacheKey, $this->lifeTime);
        }
    }

    private function getCacheKey(string $databaseUri, string $path, array $value) : string
    {
        return md5($databaseUri . '/' . $path . ':' . \GuzzleHttp\json_encode($value));
    }
}
