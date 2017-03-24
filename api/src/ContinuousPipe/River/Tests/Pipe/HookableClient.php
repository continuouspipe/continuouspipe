<?php

namespace ContinuousPipe\River\Tests\Pipe;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise\PromiseInterface;

class HookableClient implements Client
{
    /**
     * @var Client
     */
    private $decoratedClient;

    /**
     * @var callable[]
     */
    private $environmentHooks = [];

    public function __construct(Client $decoratedClient)
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
     * List environments for that given flow.
     *
     * @param string $clusterIdentifier
     * @param Team $team
     * @param User $authenticatedUser
     *
     * @throws ClusterNotFound
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser)
    {
        $result = $this->decoratedClient->getEnvironments($clusterIdentifier, $team, $authenticatedUser);

        return $this->executeHooks($result);
    }

    /**
     * @param string $clusterIdentifier
     * @param Team $team
     * @param User $authenticatedUser
     * @param array $labels
     *
     * @throws ClusterNotFound
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, User $authenticatedUser, array $labels)
    {
        $result = $this->decoratedClient->getEnvironmentsLabelled($clusterIdentifier, $team, $authenticatedUser, $labels);

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
