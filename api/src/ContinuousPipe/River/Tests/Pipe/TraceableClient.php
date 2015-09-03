<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\User;

class TraceableClient implements Client
{
    /**
     * @var DeploymentRequest\Target[]
     */
    private $deletions = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function start(DeploymentRequest $deploymentRequest, User $user)
    {
        return $this->client->start($deploymentRequest, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, User $user)
    {
        $this->client->deleteEnvironment($target, $user);
        $this->deletions[] = $target;
    }

    /**
     * @return DeploymentRequest\Target[]
     */
    public function getDeletions()
    {
        return $this->deletions;
    }
}
