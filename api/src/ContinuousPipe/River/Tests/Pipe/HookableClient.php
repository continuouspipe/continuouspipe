<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

class HookableClient implements Client\Client
{
    /**
     * @var Client\Client
     */
    private $decoratedClient;

    /**
     * @var callable[]
     */
    private $environmentHooks = [];

    public function __construct(Client\Client $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * @param DeploymentRequest $deploymentRequest
     * @param User $user
     *
     * @return Deployment
     */
    public function start(DeploymentRequest $deploymentRequest, User $user)
    {
        return $this->decoratedClient->start($deploymentRequest, $user);
    }

    /**
     * @param DeploymentRequest\Target $target
     * @param Team $team
     * @param User $authenticatedUser
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser)
    {
        $this->decoratedClient->deleteEnvironment($target, $team, $authenticatedUser);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName)
    {
        $this->decoratedClient->deletePod($team, $authenticatedUser, $clusterIdentifier, $namespace, $podName);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team)
    {
        $result = $this->decoratedClient->getEnvironments($clusterIdentifier, $team);

        return $this->executeHooks($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, array $labels)
    {
        $result = $this->decoratedClient->getEnvironmentsLabelled($clusterIdentifier, $team, $labels);

        return $this->executeHooks($result);
    }

    public function addEnvironmentHook(callable $hook)
    {
        $this->environmentHooks[] = $hook;
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    private function executeHooks($result)
    {
        foreach ($this->environmentHooks as $hook) {
            $result = $hook($result);
        }
        return $result;
    }
}
