<?php

namespace GitHub\Integration\Cache;

use Doctrine\Common\Cache\Cache;
use GitHub\Integration\Installation;
use GitHub\Integration\InstallationRepositoryWithCacheInvalidation;
use GitHub\Integration\InstallationToken;
use GitHub\Integration\InstallationTokenResolver;
use JMS\Serializer\SerializerInterface;

class CachedInstallationTokenResolver implements InstallationTokenResolver, InstallationRepositoryWithCacheInvalidation
{
    /**
     * @var InstallationTokenResolver
     */
    private $decoratedResolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param InstallationTokenResolver $decoratedResolver
     * @param SerializerInterface       $serializer
     * @param Cache                     $cache
     */
    public function __construct(InstallationTokenResolver $decoratedResolver, SerializerInterface $serializer, Cache $cache)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->serializer = $serializer;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Installation $installation)
    {
        $key = $this->generateCacheKey($installation);

        // This is done in order to have a cache safety threshold
        $now = new \DateTime('+1min');

        if (false !== ($serializedToken = $this->cache->fetch($key))) {
            /** @var InstallationToken $token */
            $token = $this->serializer->deserialize($serializedToken, InstallationToken::class, 'json');

            if ($token->getExpiresAt() > $now) {
                return $token;
            }
        }

        $token = $this->decoratedResolver->get($installation);

        $expirationDate = $token->getExpiresAt();
        $expirationInSeconds = $expirationDate->getTimestamp() - $now->getTimestamp();

        if ($expirationInSeconds > 0) {
            $serializedToken = $this->serializer->serialize($token, 'json');

            $this->cache->save($key, $serializedToken, $expirationInSeconds);
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(Installation $installation)
    {
        $this->cache->delete(
            $this->generateCacheKey($installation)
        );
    }

    /**
     * @param Installation $installation
     *
     * @return string
     */
    private function generateCacheKey(Installation $installation): string
    {
        return 'github_installation_' . $installation->getId();
    }
}
