<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DockerCompose\DockerComposeReader;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FlattenDeploymentRequestFactory implements DeploymentRequestFactory
{
    /**
     * @var DockerComposeReader
     */
    private $dockerComposeReader;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param DockerComposeReader   $dockerComposeReader
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(DockerComposeReader $dockerComposeReader, UrlGeneratorInterface $urlGenerator)
    {
        $this->dockerComposeReader = $dockerComposeReader;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DeployContext $context)
    {
        $dockerComposeContents = $this->dockerComposeReader->getContents($context);
        $callbackUrl = $this->urlGenerator->generate('pipe_notification_post', [
            'tideUuid' => $context->getTideUuid(),
        ]);

        return new DeploymentRequest(
            $context->getCodeReference()->getBranch(),
            $context->getProviderName(),
            $dockerComposeContents,
            $callbackUrl,
            $context->getLog()->getId()
        );
    }
}
