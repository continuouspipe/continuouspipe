<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\TideContext;

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
     * @param TideContext                   $tideContext
     * @param EnvironmentAwareConfiguration $configuration
     *
     * @return Target
     */
    public function create(TideContext $tideContext, EnvironmentAwareConfiguration $configuration)
    {
        return new Target(
            $this->environmentNamingStrategy->getName(
                $tideContext->getTideUuid(),
                $configuration->getEnvironmentName()
            ),
            $configuration->getClusterIdentifier(),
            [
                'flow' => (string) $tideContext->getFlowUuid(),
            ]
        );
    }
}
