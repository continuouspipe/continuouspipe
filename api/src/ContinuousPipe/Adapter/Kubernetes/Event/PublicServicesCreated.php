<?php

namespace ContinuousPipe\Adapter\Kubernetes\Event;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Pipe\DeploymentContext;

class PublicServicesCreated
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var ComponentCreationStatus
     */
    private $status;

    /**
     * @param DeploymentContext       $context
     * @param ComponentCreationStatus $status
     */
    public function __construct(DeploymentContext $context, ComponentCreationStatus $status)
    {
        $this->context = $context;
        $this->status = $status;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ComponentCreationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
