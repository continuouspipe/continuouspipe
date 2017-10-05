<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\WaitReadiness\EventListener\PublicServicesCreated;

use ContinuousPipe\Pipe\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Pipe\Kubernetes\Event\PublicServicesCreated;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\EndpointException;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\EndpointNotFound;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\PublicEndpointObjectVoter;
use ContinuousPipe\Pipe\Kubernetes\PublicEndpoint\PublicEndpointWaiter;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\PublicEndpointsReady;
use Kubernetes\Client\Model\KubernetesObject;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use React;

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
     * @var PublicEndpointObjectVoter
     */
    private $publicEndpointObjectVoter;

    public function __construct(
        MessageBus $eventBus,
        PublicEndpointWaiter $waiter,
        LoggerFactory $loggerFactory,
        PublicEndpointObjectVoter $publicEndpointObjectVoter
    ) {
        $this->eventBus = $eventBus;
        $this->waiter = $waiter;
        $this->loggerFactory = $loggerFactory;
        $this->publicEndpointObjectVoter = $publicEndpointObjectVoter;
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
            $endpoints = $this->waitEndpoints($context, $objects);

            $this->eventBus->handle(new PublicEndpointsReady($context, $endpoints));
        } catch (EndpointNotFound $e) {
            $this->eventBus->handle(new DeploymentFailed($context));
        }
    }

    /**
     * @param DeploymentContext  $context
     * @param KubernetesObject[] $objects
     *
     * @return \ContinuousPipe\Pipe\Environment\PublicEndpoint[]
     *
     * @throws \Exception
     */
    private function waitEndpoints(DeploymentContext $context, array $objects)
    {
        $loop = React\EventLoop\Factory::create();

        $waitPromises = array_map(function (KubernetesObject $object) use ($loop, $context) {
            return $this->waiter->waitEndpoints($loop, $context, $object);
        }, $objects);

        $endpoints = [];
        $exception = null;

        React\Promise\all($waitPromises)->then(function (array $foundEndpointsPerObject) use (&$endpoints) {
            foreach ($foundEndpointsPerObject as $foundEndpoints) {
                $endpoints = array_merge($endpoints, $foundEndpoints);
            }
        }, function (EndpointException $e) use (&$exception) {
            $exception = $e;
        });

        $loop->run();

        if ($exception instanceof \Exception) {
            throw $exception;
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
            return $this->publicEndpointObjectVoter->isThePrimaryPublicEndpointToWait($object);
        });

        return array_values($objects);
    }
}
