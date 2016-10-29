<?php

namespace GitHub\Integration\RedisCache;

use GitHub\Integration\Installation;
use GitHub\Integration\InstallationToken;
use GitHub\Integration\InstallationTokenResolver;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;

class PredisCachedInstallationTokenResolver implements InstallationTokenResolver
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
        $key = 'github_installation_'.$installation->getId();
        if (!empty($serializedToken = $this->redisClient->get($key))) {
            return $this->serializer->deserialize($serializedToken, InstallationToken::class, 'json');
        }

        $token = $this->decoratedResolver->get($installation);
        $expirationInSeconds = (new \DateTime('now'))->getTimestamp() - $token->getExpiresAt()->getTimestamp();
        $expirationInSecondsWithSafetyThreshold = $expirationInSeconds - 60;

        if ($expirationInSecondsWithSafetyThreshold > 0) {
            $serializedToken = $this->serializer->serialize($token, 'json');

            $this->redisClient->setex($key, $expirationInSecondsWithSafetyThreshold, $serializedToken);
        }

        return $token;
    }
}
