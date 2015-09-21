<?php

namespace ContinuousPipe\Adapter\HttpLabs\Handler;

use ContinuousPipe\Adapter\HttpLabs\Endpoint\EndpointCouldNotBeProxied;
use ContinuousPipe\Adapter\HttpLabs\Endpoint\EndpointProxier;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\PublicEndpointsFinalised;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus      $eventBus
     * @param EndpointProxier $proxier
     * @param LoggerFactory   $loggerFactory
     */
    public function __construct(MessageBus $eventBus, EndpointProxier $proxier, LoggerFactory $loggerFactory)
    {
        $this->eventBus = $eventBus;
        $this->proxier = $proxier;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param ProxyPublicEndpointsCommand $command
     *
     * @throws \ContinuousPipe\Adapter\HttpLabs\Endpoint\EndpointCouldNotBeProxied
     * @throws \Exception
     */
    public function handle(ProxyPublicEndpointsCommand $command)
    {
        $deploymentId = $command->getContext()->getDeployment()->getUuid();
        $environment = $command->getContext()->getEnvironment();

        $log = $this->loggerFactory->from($command->getContext()->getLog())->append(
            new Text('Proxy endpoints')
        );
        $logger = $this->loggerFactory->from($log);
        $logger->start();

        $createEndpoint = function (PublicEndpoint $endpoint) use ($deploymentId, $environment, $logger) {
            $publicEndpoint = new PublicEndpoint($endpoint->getName(), $this->proxier->createProxy(
                $endpoint,
                sprintf('%s-%s', $deploymentId, $endpoint->getName()),
                $environment->getComponent($endpoint->getName())
            ));
            $logger->append(new Text(sprintf('Proxied endpoint "%s"', $endpoint->getName())));

            return $publicEndpoint;
        };

        try {
            $proxiedEndpoints = array_map($createEndpoint, $command->getEndpoints());
        } catch (EndpointCouldNotBeProxied $e) {
            $logger->append(new Text('Error: '.$e->getMessage()));
            $logger->failure();
            $this->eventBus->handle(new DeploymentFailed($command->getContext()->getDeployment()->getUuid()));
            throw $e;
        }

        $logger->success();

        $this->eventBus->handle(new PublicEndpointsFinalised($command->getContext(), $proxiedEndpoints));
    }
}
