<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\User;

interface Client
{
    /**
     * @param DeploymentRequest $deploymentRequest
     * @param User              $user
     *
     * @return Deployment
     */
    public function start(DeploymentRequest $deploymentRequest, User $user);

    /**
     * @param DeploymentRequest\Target $target
     * @param User $user
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, User $user);
}
