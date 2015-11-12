<?php

namespace ContinuousPipe\Pipe\Command;

use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\View\ComponentStatus;

class WaitComponentsCommand implements DeploymentCommand
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var \ContinuousPipe\Pipe\View\ComponentStatus[]
     */
    private $componentStatuses;

    /**
     * @param DeploymentContext $context
     * @param ComponentStatus[] $componentStatuses
     */
    public function __construct(DeploymentContext $context, array $componentStatuses)
    {
        $this->context = $context;
        $this->componentStatuses = $componentStatuses;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \ContinuousPipe\Pipe\View\ComponentStatus[]
     */
    public function getComponentStatuses()
    {
        return $this->componentStatuses;
    }
}
