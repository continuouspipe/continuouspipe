<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use ContinuousPipe\User\User;

class FakeClient implements Client
{
    /**
     * {@inheritdoc}
     */
    public function start(EnvironmentDeploymentRequest $deploymentRequest, User $user)
    {
    }
}
