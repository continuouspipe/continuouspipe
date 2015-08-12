<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;

interface Client
{
    /**
     * @param EnvironmentDeploymentRequest $deploymentRequest
     */
    public function start(EnvironmentDeploymentRequest $deploymentRequest);
}
