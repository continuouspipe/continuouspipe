<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Promise\PromiseInterface;

interface DeployedEnvironmentRepository
{
    /**
     * Find the deployed environments of this flow.
     *
     * @param FlatFlow $flow
     *
     * @throws DeployedEnvironmentException
     *
     * @return DeployedEnvironment[]
     */
    public function findByFlow(FlatFlow $flow);

    /**
     * Find the deployed environments of this flow, for the given cluster.
     *
     * @param FlatFlow $flow
     * @param string $clusterIdentifier
     *
     * @throws DeployedEnvironmentException
     *
     * @return PromiseInterface|DeployedEnvironment[]
     */
    public function findByFlowAndCluster(FlatFlow $flow, string $clusterIdentifier) : PromiseInterface;

    /**
     * Delete a deployed environment.
     *
     * @param Team $team
     * @param User $user
     * @param DeployedEnvironment $environment
     *
     * @throws DeployedEnvironmentException
     */
    public function delete(Team $team, User $user, DeployedEnvironment $environment);

    /**
     * Delete a Pod.
     *
     * @param FlatFlow $flow
     * @param string $clusterIdentifier
     * @param string $namespace
     * @param string $podName
     *
     * @throws DeployedEnvironmentException
     */
    public function deletePod(FlatFlow $flow, string $clusterIdentifier, string $namespace, string $podName);
}
