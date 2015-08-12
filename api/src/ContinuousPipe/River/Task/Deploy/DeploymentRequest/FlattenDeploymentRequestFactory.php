<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DockerCompose\DockerComposeReader;

class FlattenDeploymentRequestFactory implements DeploymentRequestFactory
{
    /**
     * @var DockerComposeReader
     */
    private $dockerComposeReader;

    /**
     * @param DockerComposeReader $dockerComposeReader
     */
    public function __construct(DockerComposeReader $dockerComposeReader)
    {
        $this->dockerComposeReader = $dockerComposeReader;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DeployContext $context)
    {
        $dockerComposeContents = $this->dockerComposeReader->getContents($context);

        return new EnvironmentDeploymentRequest(
            $context->getCodeReference()->getBranch(),
            $context->getProviderName(),
            $dockerComposeContents,
            $context->getLog()->getId()
        );
    }
}
