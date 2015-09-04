<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class FakeClient implements Client
{
    /**
     * @var Environment[]
     */
    private $environments = [];

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

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, User $user)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($providerName, User $user)
    {
        return $this->environments;
    }

    /**
     * @param Environment $environment
     */
    public function addEnvironment(Environment $environment)
    {
        $this->environments[] = $environment;
    }
}
