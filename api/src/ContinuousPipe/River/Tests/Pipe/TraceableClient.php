<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\User;

class TraceableClient implements Client
{
    /**
     * @var DeploymentRequest\Target[]
     */
    private $deletions = [];

    /**
     * @var DeploymentRequest[]
     */
    private $requests;

    /**
     * @var Client\Deployment|null
     */
    private $lastDeployment;

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
        $deployment = $this->client->start($deploymentRequest, $user);

        $this->requests[] = $deploymentRequest;
        $this->lastDeployment = $deployment;

        return $deployment;
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
     * {@inheritdoc}
     */
    public function getEnvironments($providerName, User $user)
    {
        return $this->client->getEnvironments($providerName, $user);
    }

    /**
     * @return DeploymentRequest\Target[]
     */
    public function getDeletions()
    {
        return $this->deletions;
    }

    /**
     * @return Client\DeploymentRequest[]
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @return Client\Deployment|null
     */
    public function getLastDeployment()
    {
        return $this->lastDeployment;
    }
}
