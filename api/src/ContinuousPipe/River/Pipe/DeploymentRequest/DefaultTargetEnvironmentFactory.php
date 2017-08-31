<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\Tide;

class DefaultTargetEnvironmentFactory implements TargetEnvironmentFactory
{
    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     */
    public function __construct(EnvironmentNamingStrategy $environmentNamingStrategy)
    {
        $this->environmentNamingStrategy = $environmentNamingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, EnvironmentAwareConfiguration $configuration) : Target
    {
        return new Target(
            $this->environmentNamingStrategy->getName(
                $tide,
                $configuration->getEnvironmentName()
            ),
            $configuration->getClusterIdentifier(),
            [
                'flow' => (string) $tide->getFlowUuid(),
            ]
        );
    }
}
