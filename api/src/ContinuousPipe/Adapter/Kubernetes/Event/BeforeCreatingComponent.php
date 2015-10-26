<?php

namespace ContinuousPipe\Adapter\Kubernetes\Event;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\NamespaceClient;
use Symfony\Component\EventDispatcher\Event;

class BeforeCreatingComponent extends Event
{
    const NAME = 'before_creating_component';

    /**
     * @var NamespaceClient
     */
    private $client;

    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @var Component
     */
    private $component;

    /**
     * @param NamespaceClient   $client
     * @param DeploymentContext $context
     * @param Component         $component
     */
    public function __construct(NamespaceClient $client, DeploymentContext $context, Component $component)
    {
        $this->client = $client;
        $this->context = $context;
        $this->component = $component;
    }

    /**
     * @return NamespaceClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Component
     */
    public function getComponent()
    {
        return $this->component;
    }
}
