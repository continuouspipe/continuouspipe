<?php

namespace GitHub\Integration\RedisCache;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
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
    public function findByRepository(GitHubCodeRepository $codeRepository)
    {
        $key = 'github_installation_by_repository_'.md5($codeRepository->getIdentifier().'_'.$codeRepository->getAddress());

        if (!empty($serializedInstallation = $this->client->get($key))) {
            $installation = $this->serializer->deserialize($serializedInstallation, Installation::class, 'json');
        } else {
            $installation = $this->decoratedRepository->findByRepository($codeRepository);
            $metaKey = $this->generateMetaKey($installation);
            $keys = $this->client->get($metaKey) ?: [];
            $keys = array_merge($keys, [$key]);
            $serializedInstallation = $this->serializer->serialize($installation, 'json');

            $this->client->setex($key, $this->expirationInSeconds, $serializedInstallation);
            $this->client->setex($metaKey, $this->expirationInSeconds, $this->serializer->serialize($keys, 'json'));
        }

        return $installation;
    }

    public function invalidate(Installation $installation)
    {
        $metaKey = $this->generateMetaKey($installation);
        $serializedKeys = $this->client->get($metaKey);
        $keys = is_null($serializedKeys) ? [] : $this->serializer->deserialize($serializedKeys, 'array', 'json');
        $keys[] = $metaKey;
        $this->client->del($keys);
    }

    private function generateMetaKey(Installation $installation)
    {
        return 'github_repositories_by_installation_' . $installation->getId();
    }
}
