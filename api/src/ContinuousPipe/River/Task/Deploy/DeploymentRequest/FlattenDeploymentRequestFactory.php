<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FlattenDeploymentRequestFactory implements DeploymentRequestFactory
{
    /**
     * @var TargetEnvironmentFactory
     */
    private $targetEnvironmentFactory;

    public function __construct(TargetEnvironmentFactory $targetEnvironmentFactory)
    {
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $bucketUuid = $tide->getTeam()->getBucketUuid();

        return new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification(
                $configuration->getComponents()
            ),
            $bucketUuid,
            new DeploymentRequest\Notification(
                null,
                $taskDetails->getLogId()
            ),
            [
                'tide_uuid' => $tide->getUuid()->toString(),
                'task' => 'deploy',
            ]
        );
    }
}
