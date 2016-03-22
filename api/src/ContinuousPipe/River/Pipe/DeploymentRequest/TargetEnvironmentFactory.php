<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use Rhumsaa\Uuid\Uuid;

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
     * @param Uuid                          $tideUuid
     * @param EnvironmentAwareConfiguration $configuration
     *
     * @return Target
     */
    public function create(Uuid $tideUuid, EnvironmentAwareConfiguration $configuration)
    {
        return new Target(
            $this->environmentNamingStrategy->getName(
                $tideUuid,
                $configuration->getEnvironmentName()
            ),
            $configuration->getClusterIdentifier()
        );
    }
}
