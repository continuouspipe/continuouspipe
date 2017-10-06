<?php

namespace ContinuousPipe\Pipe\Client;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Pipe\Client\PipeClientException;
use ContinuousPipe\Pipe\ClusterNotFound;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise\PromiseInterface;

interface Client
{
    /**
     * @param DeploymentRequest $deploymentRequest
     * @param User              $user
     *
     * @throws PipeClientException
     *
     * @return Deployment
     */
    public function start(DeploymentRequest $deploymentRequest, User $user);

    /**
     * @param DeploymentRequest\Target $target
     * @param Team                     $team
     * @param User                     $authenticatedUser
     *
     * @throws PipeClientException
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser);

    /**
     * Delete a Pod.
     *
     * @param Team $team
     * @param User $authenticatedUser
     * @param string $clusterIdentifier
     * @param string $namespace
     * @param string $podName
     *
     * @throws PipeClientException
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName);

    /**
     * List environments for that given flow.
     *
     * @param string $clusterIdentifier
     * @param Team   $team
     *
     * @throws ClusterNotFound
     * @throws PipeClientException
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironments($clusterIdentifier, Team $team);

    /**
     * @param string $clusterIdentifier
     * @param Team   $team
     * @param array  $labels
     *
     * @throws ClusterNotFound
     * @throws PipeClientException
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, array $labels);
}
