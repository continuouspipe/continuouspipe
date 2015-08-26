<?php

namespace ContinuousPipe\Adapter\Kubernetes\Event;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\Service;

class PublicServicesCreated
{
    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var Service[]
     */
    private $services;

    /**
     * @param DeploymentContext $context
     * @param Service[]         $services
     */
    public function __construct(DeploymentContext $context, array $services)
    {
        $this->context = $context;
        $this->services = $services;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \Kubernetes\Client\Model\Service[]
     */
    public function getServices()
    {
        return $this->services;
    }
}
