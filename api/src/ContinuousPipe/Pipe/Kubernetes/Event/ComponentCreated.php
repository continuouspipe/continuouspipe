<?php

namespace ContinuousPipe\Pipe\Kubernetes\Event;

use ContinuousPipe\Pipe\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\DeploymentContext;

class ComponentCreated
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var Component
     */
    private $component;

    /**
     * @var ComponentCreationStatus
     */
    private $status;

    /**
     * @param DeploymentContext       $context
     * @param Component               $component
     * @param ComponentCreationStatus $status
     */
    public function __construct(DeploymentContext $context, Component $component, ComponentCreationStatus $status)
    {
        $this->context = $context;
        $this->component = $component;
        $this->status = $status;
    }

    /**
     * @return Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @return ComponentCreationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
