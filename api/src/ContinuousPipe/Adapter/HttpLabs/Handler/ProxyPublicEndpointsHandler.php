<?php

namespace ContinuousPipe\Adapter\HttpLabs\Handler;

use ContinuousPipe\Adapter\HttpLabs\Endpoint\EndpointProxier;
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
     * @var GuzzleEndpointProxier
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
        $deploymentId = $command->getContext()->getDeployment()->getUuid();
        $environment = $command->getContext()->getEnvironment();

        $proxiedEndpoints = array_map(
            function (PublicEndpoint $endpoint) use ($deploymentId, $environment) {
                return new PublicEndpoint($endpoint->getName(), $this->proxier->createProxy(
                    $endpoint,
                    sprintf('%s-%s', $deploymentId, $endpoint->getName()),
                    $environment->getComponent($endpoint->getName())
                ));
            },
            $command->getEndpoints()
        );

        $this->eventBus->handle(new PublicEndpointsFinalised($command->getContext(), $proxiedEndpoints));
    }
}
