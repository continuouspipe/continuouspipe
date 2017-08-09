<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise\PromiseInterface;

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
     * @param Team                     $team
     * @param User                     $authenticatedUser
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser);

    /**
     * List environments for that given flow.
     *
     * @param string $clusterIdentifier
     * @param Team   $team
     * @param User   $authenticatedUser
     *
     * @throws ClusterNotFound
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser);

    /**
     * @param string $clusterIdentifier
     * @param Team   $team
     * @param User   $authenticatedUser
     * @param array  $labels
     *
     * @throws ClusterNotFound
     *
     * @return PromiseInterface Returns an array of \ContinuousPipe\Model\Environment objects when unwrapped.
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, User $authenticatedUser, array $labels);

    /**
     * Delete a Pod.
     *
     * @param Team $team
     * @param User $authenticatedUser
     * @param string $clusterIdentifier
     * @param string $namespace
     * @param string $podName
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName);
}
