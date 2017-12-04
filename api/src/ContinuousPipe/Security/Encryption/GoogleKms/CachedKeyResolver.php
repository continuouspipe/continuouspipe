<?php

namespace ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\EncryptionException;
use Doctrine\Common\Cache\Cache;

class CachedKeyResolver implements GoogleKmsKeyResolver
{
    /**
     * @var GoogleKmsKeyResolver
     */
    private $decoratedResolver;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifeTime;

    public function __construct(GoogleKmsKeyResolver $decoratedResolver, Cache $cache, int $lifeTime = 3600)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function keyName(string $namespace): string
    {
        $cacheKey = 'kms:key-for:'.md5($namespace);
        if (false === ($keyName = $this->cache->fetch($cacheKey))) {
            $keyName = $this->decoratedResolver->keyName($namespace);

            $this->cache->save($cacheKey, $keyName, $this->lifeTime);
        }

        return $keyName;
    }
}
