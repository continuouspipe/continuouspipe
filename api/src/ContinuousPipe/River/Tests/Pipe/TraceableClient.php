<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

class TraceableClient implements Client
{
    /**
     * @var DeploymentRequest\Target[]
     */
    private $deletions = [];

    /**
     * @var DeploymentRequest[]
     */
    private $requests = [];

    /**
     * @var Client\Deployment[]
     */
    private $deployments = [];

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
        $this->deployments[] = $deployment;

        return $deployment;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser)
    {
        $this->client->deleteEnvironment($target, $team, $authenticatedUser);
        $this->deletions[] = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser)
    {
        return $this->client->getEnvironments($clusterIdentifier, $team, $authenticatedUser);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, User $authenticatedUser, array $labels)
    {
        return $this->client->getEnvironmentsLabelled($clusterIdentifier, $team, $authenticatedUser, $labels);
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
     * @return Client\Deployment[]
     */
    public function getDeployments()
    {
        return $this->deployments;
    }
}
