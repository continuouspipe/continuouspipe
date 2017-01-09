<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
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
     * @param UrlGeneratorInterface    $urlGenerator
     * @param TargetEnvironmentFactory $targetEnvironmentFactory
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TargetEnvironmentFactory $targetEnvironmentFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $callbackUrl = $this->urlGenerator->generate('pipe_notification_post', [
            'tideUuid' => $tide->getUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $bucketUuid = $tide->getTeam()->getBucketUuid();

        return new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification(
                $configuration->getComponents()
            ),
            new DeploymentRequest\Notification(
                $callbackUrl,
                $taskDetails->getLogId()
            ),
            $bucketUuid
        );
    }
}
