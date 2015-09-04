<?php

namespace ContinuousPipe\Adapter\HttpLabs\Handler;

use ContinuousPipe\Adapter\HttpLabs\EndpointProxier;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\PublicEndpointsFinalised;
use SimpleBus\Message\Bus\MessageBus;

class ProxyPublicEndpointsHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var EndpointProxier
     */
    private $proxier;

    /**
     * @param MessageBus $eventBus
     * @param EndpointProxier $proxier
     */
    public function __construct(MessageBus $eventBus, EndpointProxier $proxier)
    {
        $this->eventBus = $eventBus;
        $this->proxier = $proxier;
    }

    /**
     * @param ProxyPublicEndpointsCommand $command
     */
    public function handle(ProxyPublicEndpointsCommand $command)
    {
        $proxiedEndpoints = array_map(
            function (PublicEndpoint $endpoint) {
                return new PublicEndpoint($endpoint->getName(), $this->proxier->createProxy($endpoint));
            },
            $command->getEndpoints()
        );

        $this->eventBus->handle(new PublicEndpointsFinalised($command->getContext(), $proxiedEndpoints));
    }
}
