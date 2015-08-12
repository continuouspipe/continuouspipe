<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use ContinuousPipe\User\User;

interface Client
{
    /**
     * @param EnvironmentDeploymentRequest $deploymentRequest
     * @param User $user
     */
    public function start(EnvironmentDeploymentRequest $deploymentRequest, User $user);
}
