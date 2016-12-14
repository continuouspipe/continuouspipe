<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Tide;

class TargetEnvironmentFactory
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
     * Create the target environment descriptor.
     *
     * @param Tide                          $tide
     * @param EnvironmentAwareConfiguration $configuration
     *
     * @return Target
     */
    public function create(Tide $tide, EnvironmentAwareConfiguration $configuration)
    {
        return new Target(
            $this->environmentNamingStrategy->getName(
                $tide->getUuid(),
                $configuration->getEnvironmentName()
            ),
            $configuration->getClusterIdentifier(),
            [
                'flow' => (string) $tide->getFlowUuid(),
            ]
        );
    }
}
