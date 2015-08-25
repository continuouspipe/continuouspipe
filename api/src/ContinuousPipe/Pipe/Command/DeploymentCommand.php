<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\DeploymentContext;

interface DeploymentCommand
{
    /**
     * @return DeploymentContext
     */
    public function getContext();
}
