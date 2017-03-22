<?php

namespace GitHub\Integration\RedisCache;

use GitHub\Integration\Installation;
use GitHub\Integration\InstallationRepositoryWithCacheInvalidation;
use GitHub\Integration\InstallationToken;
use GitHub\Integration\InstallationTokenResolver;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;

class PredisCachedInstallationTokenResolver implements InstallationTokenResolver, InstallationRepositoryWithCacheInvalidation
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
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * @param InstallationTokenResolver $decoratedResolver
     * @param SerializerInterface       $serializer
     * @param ClientInterface           $redisClient
     */
    public function __construct(InstallationTokenResolver $decoratedResolver, SerializerInterface $serializer, ClientInterface $redisClient)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->serializer = $serializer;
        $this->redisClient = $redisClient;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Installation $installation)
    {
        $key = $this->generateCacheKey($installation);

        // This is done in order to have a cache safety threshold
        $now = new \DateTime('+1min');

        if (!empty($serializedToken = $this->redisClient->get($key))) {
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

            $this->redisClient->setex($key, $expirationInSeconds, $serializedToken);
        }

        return $token;
    }

    public function invalidate(Installation $installation)
    {
        $key = $this->generateCacheKey($installation);
        $this->redisClient->del([$key]);
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
