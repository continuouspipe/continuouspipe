<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\River\Flow;

interface DeployedEnvironmentRepository
{
    /**
     * Find the deployed environments of this flow.
     *
     * @param Flow $flow
     *
     * @return DeployedEnvironment[]
     */
    public function findByFlow(Flow $flow);

    /**
     * Delete a deployed environment.
     *
     * @param Flow                $flow
     * @param DeployedEnvironment $environment
     */
    public function delete(Flow $flow, DeployedEnvironment $environment);
}
