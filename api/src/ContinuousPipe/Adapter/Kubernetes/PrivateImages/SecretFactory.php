<?php

namespace ContinuousPipe\Adapter\Kubernetes\PrivateImages;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\User\Authenticator\AuthenticatorClient;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;

class SecretFactory
{
    const SECRET_NAME = 'continuousPipeDockerRegistries';

    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @var DockerCfgFileGenerator
     */
    private $dockerCfgFileGenerator;

    /**
     * @param AuthenticatorClient    $authenticatorClient
     * @param DockerCfgFileGenerator $dockerCfgFileGenerator
     */
    public function __construct(AuthenticatorClient $authenticatorClient, DockerCfgFileGenerator $dockerCfgFileGenerator)
    {
        $this->authenticatorClient = $authenticatorClient;
        $this->dockerCfgFileGenerator = $dockerCfgFileGenerator;
    }

    /**
     * @param DeploymentContext $deploymentContext
     *
     * @return Secret
     */
    public function createDockerRegistrySecret(DeploymentContext $deploymentContext)
    {
        $user = $deploymentContext->getDeployment()->getUser();
        $credentials = $this->authenticatorClient->getDockerCredentialsByUserEmail($user->getEmail());
        $dockerCfgFileContents = $this->dockerCfgFileGenerator->generate($credentials);

        return new Secret(
            new ObjectMetadata(self::SECRET_NAME),
            base64_encode($dockerCfgFileContents),
            'kubernetes.io/dockercfg'
        );
    }
}
