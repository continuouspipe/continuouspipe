<?php

namespace ContinuousPipe\Pipe\Kubernetes\PrivateImages;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Uuid\UuidTransformer;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\User\User;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;
use Ramsey\Uuid\UuidInterface;

class SecretFactory
{
    const SECRET_PREFIX = 'cp-dock-registry-';

    /**
     * @var DockerCfgFileGenerator
     */
    private $dockerCfgFileGenerator;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param DockerCfgFileGenerator $dockerCfgFileGenerator
     * @param BucketRepository       $bucketRepository
     */
    public function __construct(DockerCfgFileGenerator $dockerCfgFileGenerator, BucketRepository $bucketRepository)
    {
        $this->dockerCfgFileGenerator = $dockerCfgFileGenerator;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @param DeploymentContext $deploymentContext
     *
     * @return Secret
     */
    public function createDockerRegistrySecret(DeploymentContext $deploymentContext)
    {
        $credentialsBucketUuid = $deploymentContext->getDeployment()->getRequest()->getCredentialsBucket();
        $bucket = $this->bucketRepository->find(UuidTransformer::transform($credentialsBucketUuid));

        $dockerCfgFileContents = $this->dockerCfgFileGenerator->generate($bucket);

        return new Secret(
            new ObjectMetadata(
                $this->getSecretName($credentialsBucketUuid)
            ),
            [
                '.dockercfg' => base64_encode($dockerCfgFileContents),
            ],
            'kubernetes.io/dockercfg'
        );
    }

    private function getSecretName(UuidInterface $uuid) : string
    {
        $userIdentifier = (new Slugify())->slugify('bucket-'.$uuid->toString());

        return self::SECRET_PREFIX.$userIdentifier;
    }
}
