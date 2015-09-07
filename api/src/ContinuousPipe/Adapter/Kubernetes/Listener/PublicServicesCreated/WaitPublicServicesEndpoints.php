<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\PublicServicesCreated;

use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\ServiceWaiter;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use Kubernetes\Client\Model\Service;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus    $eventBus
     * @param ServiceWaiter $waiter
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $eventBus, ServiceWaiter $waiter, LoggerFactory $loggerFactory)
    {
        $this->eventBus = $eventBus;
        $this->waiter = $waiter;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param PublicServicesCreated $event
     */
    public function notify(PublicServicesCreated $event)
    {
        $context = $event->getContext();

        $log = $this->loggerFactory->from($context->getLog())->append(new Text('Waiting public endpoints to be created'));
        $logger = $this->loggerFactory->from($log);
        $logger->start();

        try {
            $endpoints = $this->waitEndpoints($context, $event->getServices(), $logger->getLog());
            $logger->success();
            $this->eventBus->handle(new PublicEndpointsCreated($context, $endpoints));
        } catch (EndpointNotFound $e) {
            $logger->failure();

            $this->eventBus->handle(new DeploymentFailed($context->getDeployment()->getUuid()));
        }
    }

    /**
     * @param DeploymentContext $context
     * @param Service[]         $services
     * @param Log               $log
     *
     * @return \ContinuousPipe\Pipe\Environment\PublicEndpoint[]
     */
    private function waitEndpoints(DeploymentContext $context, array $services, Log $log)
    {
        $endpoints = [];
        foreach ($services as $service) {
            $endpoints[] = $this->waiter->waitService($context, $service, $log);
        }

        return $endpoints;
    }
}
