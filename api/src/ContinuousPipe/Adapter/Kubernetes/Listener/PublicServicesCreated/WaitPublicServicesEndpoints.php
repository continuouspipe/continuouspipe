<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\PublicServicesCreated;

use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ServiceWaiter;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use Kubernetes\Client\Model\Service;
use SimpleBus\Message\Bus\MessageBus;

class WaitPublicServicesEndpoints
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var ServiceWaiter
     */
    private $waiter;

    /**
     * @param MessageBus    $eventBus
     * @param ServiceWaiter $waiter
     */
    public function __construct(MessageBus $eventBus, ServiceWaiter $waiter)
    {
        $this->eventBus = $eventBus;
        $this->waiter = $waiter;
    }

    /**
     * @param PublicServicesCreated $event
     */
    public function notify(PublicServicesCreated $event)
    {
        $context = $event->getContext();

        try {
            $endpoints = $this->waitEndpoints($context, $event->getServices());

            $this->eventBus->handle(new PublicEndpointsCreated($context, $endpoints));
        } catch (EndpointNotFound $e) {
            $this->eventBus->handle(new DeploymentFailed($context->getDeployment()->getUuid()));
        }
    }

    /**
     * @param DeploymentContext $context
     * @param Service[] $services
     * @return PublicEndpoint[]
     */
    private function waitEndpoints(DeploymentContext $context, array $services)
    {
        $endpoints = [];
        foreach ($services as $service) {
            $endpoints[] = $this->waiter->waitService($context, $service);
        }

        return $endpoints;
    }
}
