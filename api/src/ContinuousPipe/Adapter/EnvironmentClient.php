<?php

namespace ContinuousPipe\Adapter;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\DeploymentContext;

interface EnvironmentClient
{
    /**
     * @param Environment       $environment
     * @param DeploymentContext $deploymentContext
     *
     * @return Environment
     */
    public function createOrUpdate(Environment $environment, DeploymentContext $deploymentContext);

    /**
     * List environments.
     *
     * @return Environment
     */
    public function findAll();
}
