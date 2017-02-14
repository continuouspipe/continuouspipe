<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Cache;

use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use Doctrine\Common\Cache\Cache;
use Predis\Connection\ConnectionException;
use Predis\PredisException;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $lifeTime;

    public function __construct(FirebaseClient $decoratedClient, Cache $cache, LoggerInterface $logger, int $lifeTime = 300)
    {
        $this->decoratedClient = $decoratedClient;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $databaseUri, string $path, array $value)
    {
        $cacheKey = $this->getCacheKey($databaseUri, $path, $value);

        if (!$this->contains($cacheKey)) {
            $this->decoratedClient->set($databaseUri, $path, $value);

            $this->save($cacheKey, $cacheKey, $this->lifeTime);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $databaseUri, string $path, array $value)
    {
        $cacheKey = $this->getCacheKey($databaseUri, $path, $value);

        if (!$this->contains($cacheKey)) {
            $this->decoratedClient->update($databaseUri, $path, $value);

            $this->save($cacheKey, $cacheKey, $this->lifeTime);
        }
    }

    private function getCacheKey(string $databaseUri, string $path, array $value) : string
    {
        return md5($databaseUri . '/' . $path . ':' . \GuzzleHttp\json_encode($value));
    }

    private function contains(string $key) : bool
    {
        try {
            return $this->cache->contains($key);
        } catch (PredisException $e) {
            $this->logger->warning('Unable to get the cache', [
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function save(string $key, $data, int $lifeTime)
    {
        try {
            $this->cache->save($key, $data, $lifeTime);
        } catch (PredisException $e) {
            $this->logger->warning('Unable to save in the cache', [
                'exception' => $e,
            ]);
        }
    }
}
