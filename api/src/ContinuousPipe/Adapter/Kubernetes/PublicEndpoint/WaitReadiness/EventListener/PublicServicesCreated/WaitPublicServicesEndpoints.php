<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\WaitReadiness\EventListener\PublicServicesCreated;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Adapter\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\PublicEndpointWaiter;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use ContinuousPipe\Pipe\Event\PublicEndpointsReady;
use Kubernetes\Client\Model\KubernetesObject;
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
     * @var PublicEndpointWaiter
     */
    private $waiter;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus           $eventBus
     * @param PublicEndpointWaiter $waiter
     * @param LoggerFactory        $loggerFactory
     */
    public function __construct(MessageBus $eventBus, PublicEndpointWaiter $waiter, LoggerFactory $loggerFactory)
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
        $status = $event->getStatus();
        $objects = $this->getObjectsToWait($status);

        if (count($objects) == 0) {
            $this->eventBus->handle(new PublicEndpointsReady($context, []));

            return;
        }

        try {
            $endpoints = $this->waitEndpoints($context, $objects, $context->getLog());

            if (count($status->getCreated()) > 0) {
                $this->eventBus->handle(new PublicEndpointsCreated($context, $endpoints));
            }

            $this->eventBus->handle(new PublicEndpointsReady($context, $endpoints));
        } catch (EndpointNotFound $e) {
            $this->eventBus->handle(new DeploymentFailed($context));
        }
    }

    /**
     * @param DeploymentContext  $context
     * @param KubernetesObject[] $objects
     * @param Log                $log
     *
     * @return \ContinuousPipe\Pipe\Environment\PublicEndpoint[]
     */
    private function waitEndpoints(DeploymentContext $context, array $objects, Log $log)
    {
        $endpoints = [];
        foreach ($objects as $object) {
            $endpoints[] = $this->waiter->waitEndpoint($context, $object, $log);
        }

        return $endpoints;
    }

    /**
     * @param ComponentCreationStatus $status
     *
     * @return KubernetesObject[]
     */
    private function getObjectsToWait(ComponentCreationStatus $status)
    {
        $objects = array_merge($status->getCreated(), $status->getUpdated(), $status->getIgnored());
        $objects = array_filter($objects, function (KubernetesObject $object) {
            return !$object->getMetadata()->getLabelList()->hasKey('source-of-ingress');
        });

        return array_values($objects);
    }
}
