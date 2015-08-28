<?php

namespace ContinuousPipe\Adapter\Kubernetes\PrivateImages;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\User\Authenticator\AuthenticatorClient;
use ContinuousPipe\User\User;
use Kubernetes\Client\Model\ObjectMetadata;
use Kubernetes\Client\Model\Secret;

class SecretFactory
{
    const SECRET_PREFIX = 'cp-dock-registry-';

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
            new ObjectMetadata(
                $this->getSecretName($user)
            ),
            [
                '.dockercfg' => base64_encode($dockerCfgFileContents),
            ],
            'kubernetes.io/dockercfg'
        );
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function getSecretName(User $user)
    {
        $userIdentifier = (new Slugify())->slugify($user->getEmail());

        return self::SECRET_PREFIX.$userIdentifier;
    }
}
