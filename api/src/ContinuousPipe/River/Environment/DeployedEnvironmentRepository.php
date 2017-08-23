<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

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
