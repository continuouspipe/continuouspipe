<?php

namespace ContinuousPipe\Adapter\Kubernetes\Event;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\NamespaceClient;

class AfterCreatingComponent extends BeforeCreatingComponent
{
    const NAME = 'after_creating_component';

    /**
     * @var ComponentCreationStatus
     */
    private $status;

    /**
     * @param NamespaceClient         $client
     * @param DeploymentContext       $context
     * @param Component               $component
     * @param ComponentCreationStatus $status
     */
    public function __construct(NamespaceClient $client, DeploymentContext $context, Component $component, ComponentCreationStatus $status)
    {
        parent::__construct($client, $context, $component);

        $this->status = $status;
    }

    /**
     * @return ComponentCreationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
