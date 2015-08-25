<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\DeploymentContext;

class PrepareEnvironmentCommand implements DeploymentCommand
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @param DeploymentContext $context
     */
    public function __construct(DeploymentContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
