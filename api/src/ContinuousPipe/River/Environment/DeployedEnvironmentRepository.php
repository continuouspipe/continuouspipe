<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface DeployedEnvironmentRepository
{
    /**
     * Find the deployed environments of this flow.
     *
     * @param FlatFlow $flow
     *
     * @return DeployedEnvironment[]
     */
    public function findByFlow(FlatFlow $flow);

    /**
     * Delete a deployed environment.
     *
     * @param FlatFlow            $flow
     * @param DeployedEnvironment $environment
     */
    public function delete(FlatFlow $flow, DeployedEnvironment $environment);
}
