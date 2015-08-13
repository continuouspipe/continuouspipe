<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class FakeClient implements Client
{
    /**
     * {@inheritdoc}
     */
    public function start(DeploymentRequest $deploymentRequest, User $user)
    {
        return new Client\Deployment(
            Uuid::uuid1(),
            $deploymentRequest,
            Client\Deployment::STATUS_PENDING
        );
    }
}
