<?php

namespace GitHub\Integration\RedisCache;

use GitHub\Integration\Installation;
use GitHub\Integration\InstallationRepository;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;

class PredisCachedInstallationRepository implements InstallationRepository
{
    /**
     * @var InstallationRepository
     */
    private $decoratedRepository;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var int
     */
    private $expirationInSeconds;

    /**
     * @param InstallationRepository $decoratedRepository
     * @param ClientInterface        $client
     * @param SerializerInterface    $serializer
     * @param int                    $expirationInSeconds
     */
    public function __construct(InstallationRepository $decoratedRepository, ClientInterface $client, SerializerInterface $serializer, $expirationInSeconds = 86400)
    {
        $this->decoratedRepository = $decoratedRepository;
        $this->client = $client;
        $this->serializer = $serializer;
        $this->expirationInSeconds = $expirationInSeconds;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->decoratedRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findByAccount($account)
    {
        $key = 'github_installation_by_account_'.md5($account);

        if (!empty($serializedInstallation = $this->client->get($key))) {
            $installation = $this->serializer->deserialize($serializedInstallation, Installation::class, 'json');
        } else {
            $installation = $this->decoratedRepository->findByAccount($account);
            $serializedInstallation = $this->serializer->serialize($installation, 'json');

            $this->client->setex($key, $this->expirationInSeconds, $serializedInstallation);
        }

        return $installation;
    }
}
