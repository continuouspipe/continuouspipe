<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\River\Pipe\DeploymentRequestEnhancer\DeploymentRequestEnhancer;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;

class EnhancedDeploymentRequestFactory implements DeploymentRequestFactory
{
    /**
     * @var DeploymentRequestEnhancer
     */
    private $deploymentRequestEnhancer;

    /**
     * @var DeploymentRequestFactory
     */
    private $decoratedFactory;

    /**
     * @param DeploymentRequestFactory  $decoratedFactory
     * @param DeploymentRequestEnhancer $deploymentRequestEnhancer
     */
    public function __construct(DeploymentRequestFactory $decoratedFactory, DeploymentRequestEnhancer $deploymentRequestEnhancer)
    {
        $this->deploymentRequestEnhancer = $deploymentRequestEnhancer;
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        return $this->deploymentRequestEnhancer->enhance(
            $tide,
            $taskDetails,
            $this->decoratedFactory->create($tide, $taskDetails, $configuration)
        );
    }
}
