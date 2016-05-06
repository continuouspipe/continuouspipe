<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\View\TideRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FlattenDeploymentRequestFactory implements DeploymentRequestFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TargetEnvironmentFactory
     */
    private $targetEnvironmentFactory;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param UrlGeneratorInterface    $urlGenerator
     * @param TargetEnvironmentFactory $targetEnvironmentFactory
     * @param TideRepository           $tideRepository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TargetEnvironmentFactory $targetEnvironmentFactory, TideRepository $tideRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DeployContext $context, DeployTaskConfiguration $configuration)
    {
        $callbackUrl = $this->urlGenerator->generate('pipe_notification_post', [
            'tideUuid' => $context->getTideUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $bucketUuid = $context->getTeam()->getBucketUuid();
        $tide = $this->tideRepository->find($context->getTideUuid());

        return new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification(
                $configuration->getComponents()
            ),
            new DeploymentRequest\Notification(
                $callbackUrl,
                $context->getTaskLog()->getId()
            ),
            $bucketUuid
        );
    }
}
