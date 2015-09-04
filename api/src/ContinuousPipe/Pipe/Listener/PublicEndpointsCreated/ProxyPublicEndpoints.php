<?php

namespace ContinuousPipe\Pipe\Listener\PublicEndpointsCreated;

use ContinuousPipe\Model\Component\EnvironmentVariable;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use SimpleBus\Message\Bus\MessageBus;

class ProxyPublicEndpoints
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param PublicEndpointsCreated $event
     */
    public function notify(PublicEndpointsCreated $event)
    {
        $this->commandBus->handle(
            new ProxyPublicEndpointsCommand($event->getDeploymentContext(), $event->getEndpoints())
        );
    }
}
