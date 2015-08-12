<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;

interface DeploymentRequestFactory
{
    /**
     * Create a deployment request for the pipe client based on that pipe.
     *
     * @param DeployContext $context
     *
     * @return EnvironmentDeploymentRequest
     */
    public function create(DeployContext $context);
}
