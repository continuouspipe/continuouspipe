<?php

namespace GitHub\Integration\Cache;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use Doctrine\Common\Cache\Cache;
use GitHub\Integration\Installation;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationRepositoryWithCacheInvalidation;
use JMS\Serializer\SerializerInterface;

class CachedInstallationRepository implements InstallationRepository, InstallationRepositoryWithCacheInvalidation
{
    /**
     * @var InstallationRepository
     */
    private $decoratedRepository;

    /**
     * @var Cache
     */
    private $cache;

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
     * @param Cache                  $cache
     * @param SerializerInterface    $serializer
     * @param int                    $expirationInSeconds
     */
    public function __construct(InstallationRepository $decoratedRepository, Cache $cache, SerializerInterface $serializer, $expirationInSeconds = 86400)
    {
        $this->decoratedRepository = $decoratedRepository;
        $this->cache = $cache;
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

        if (false !== ($serializedInstallation = $this->cache->fetch($key))) {
            $installation = $this->serializer->deserialize($serializedInstallation, Installation::class, 'json');
        } else {
            $installation = $this->decoratedRepository->findByRepository($codeRepository);
            $metaKey = $this->generateMetaKey($installation);
            $keys = $this->cache->fetch($metaKey) ?: [];
            $keys = array_merge($keys, [$key]);
            $serializedInstallation = $this->serializer->serialize($installation, 'json');

            $this->cache->save($key, $serializedInstallation, $this->expirationInSeconds);
            $this->cache->save($metaKey, $this->serializer->serialize($keys, 'json'), $this->expirationInSeconds);
        }

        return $installation;
    }

    public function invalidate(Installation $installation)
    {
        $metaKey = $this->generateMetaKey($installation);
        $keysToInvalidate = [$metaKey];

        if (false !== ($serializedKeys = $this->cache->fetch($metaKey))) {
            $keysToInvalidate = array_merge($keysToInvalidate, $this->serializer->deserialize($serializedKeys, 'array', 'json'));
        }

        foreach ($keysToInvalidate as $key) {
            $this->cache->delete($key);
        }
    }

    private function generateMetaKey(Installation $installation)
    {
        return 'github_repositories_by_installation_' . $installation->getId();
    }
}
