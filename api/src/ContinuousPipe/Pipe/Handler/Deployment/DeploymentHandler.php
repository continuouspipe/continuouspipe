<?php

namespace ContinuousPipe\Pipe\Handler\Deployment;

use ContinuousPipe\Pipe\DeploymentContext;

interface DeploymentHandler
{
    /**
     * Is this command handler supporting the given deployment context ?
     *
     * @param DeploymentContext $context
     *
     * @return bool
     */
    public function supports(DeploymentContext $context);
}
