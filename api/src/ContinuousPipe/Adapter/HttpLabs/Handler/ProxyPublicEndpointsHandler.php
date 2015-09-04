<?php

namespace ContinuousPipe\Adapter\HttpLabs\Handler;

use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\PublicEndpointsProxied;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use SimpleBus\Message\Bus\MessageBus;

class ProxyPublicEndpointsHandler implements DeploymentHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param ProxyPublicEndpointsCommand $command
     */
    public function handle(ProxyPublicEndpointsCommand $command)
    {
        $this->eventBus->handle(
            new PublicEndpointsProxied($command->getContext(), [
                new PublicEndpoint('api', 'badger-carrot-5678.httplabs.io'),
                new PublicEndpoint('ui', 'monkey-potato-5678.httplabs.io')
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }
}
