<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Cache;

use Doctrine\Common\Cache\Cache;
use Google\Auth\FetchAuthTokenInterface;
use Psr\Log\LoggerInterface;

class CachedAuthTokenFetcher implements FetchAuthTokenInterface
{
    /**
     * @var FetchAuthTokenInterface
     */
    private $decoratedFetcher;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(FetchAuthTokenInterface $decoratedFetcher, Cache $cache, LoggerInterface $logger)
    {
        $this->decoratedFetcher = $decoratedFetcher;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        $cacheKey = 'firebase-token:'.$this->getCacheKey();
        if (false === ($cachedData = $this->cache->fetch($cacheKey))) {
            $token = $this->decoratedFetcher->fetchAuthToken($httpHandler);
            if (isset($token['expires_at'])) {
                // Remove 30 seconds as a cache buffer
                $cacheLifeTime = $token['expires_at'] - time() - 30;
            } else {
                $this->logger->warning('No expiration in Google token', [
                    'token' => $token,
                ]);

                $cacheLifeTime = 60;
            }

            $cachedData = \GuzzleHttp\json_encode($token);
            if ($cacheLifeTime > 0) {
                $this->cache->save($cacheKey, $cachedData, $cacheLifeTime);
            } else {
                $this->logger->warning('The computed lifetime being 0 or under, the token will not be cached', [
                    'cacheLifeTime' => $cacheLifeTime,
                    'token' => $token,
                ]);
            }
        }

        return \GuzzleHttp\json_decode($cachedData, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey()
    {
        return $this->decoratedFetcher->getCacheKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastReceivedToken()
    {
        return $this->decoratedFetcher->getLastReceivedToken();
    }
}
