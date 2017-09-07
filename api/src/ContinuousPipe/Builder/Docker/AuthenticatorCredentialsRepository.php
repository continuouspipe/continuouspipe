<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\RegistryCredentials;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AuthenticatorCredentialsRepository implements CredentialsRepository
{
    /**
     * @var RegistryServerResolver
     */
    private $registryServerResolver;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param RegistryServerResolver $registryServerResolver
     * @param BucketRepository       $bucketRepository
     */
    public function __construct(RegistryServerResolver $registryServerResolver, BucketRepository $bucketRepository)
    {
        $this->registryServerResolver = $registryServerResolver;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByImage(Image $image, UuidInterface $credentialsBucketUuid)
    {
        return RegistryCredentials::fromDockerRegistryCredentials($this->findRegistryByImage($image, $credentialsBucketUuid));
    }

    /**
     * {@inheritdoc}
     */
    public function findRegistryByImage(Image $image, UuidInterface $credentialsBucketUuid): DockerRegistry
    {
        $server = $this->registryServerResolver->getServerName($image);
        $bucket = $this->bucketRepository->find($credentialsBucketUuid);

        $matchingCredentials = $bucket->getDockerRegistries()->filter(function (DockerRegistry $dockerRegistry) use ($server) {
            return $dockerRegistry->getServerAddress() == $server;
        });

        if (0 === $matchingCredentials->count()) {
            throw new CredentialsNotFound(sprintf(
                'No Docker registry credentials found for server "%s"',
                $server
            ));
        }

        return $matchingCredentials->first();
    }
}
