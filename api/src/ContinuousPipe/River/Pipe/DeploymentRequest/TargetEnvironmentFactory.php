<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest;

use ContinuousPipe\Pipe\Client\DeploymentRequest\Target;
use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;
use ContinuousPipe\River\Tide;

interface TargetEnvironmentFactory
{
    /**
     * Create the target environment descriptor.
     *
     * @param Tide                          $tide
     * @param EnvironmentAwareConfiguration $configuration
     *
     * @throws DeploymentRequestException
     *
     * @return Target
     */
    public function create(Tide $tide, EnvironmentAwareConfiguration $configuration) : Target;
}
