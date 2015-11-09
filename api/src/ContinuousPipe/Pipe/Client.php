<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

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
     * @return \ContinuousPipe\Model\Environment[]
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser);
}
