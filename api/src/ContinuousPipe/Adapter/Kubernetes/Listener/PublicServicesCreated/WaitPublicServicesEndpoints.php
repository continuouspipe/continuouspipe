<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\PublicServicesCreated;

use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ServiceWaiter;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
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
     *
     * @throws \Exception
     */
    public function notify(PublicServicesCreated $event)
    {
        $context = $event->getContext();

        $endpoints = [];
        foreach ($event->getServices() as $service) {
            $endpoints[] = $this->waiter->waitService($context, $service);
        }

        $this->eventBus->handle(new PublicEndpointsCreated($context, $endpoints));
    }
}
