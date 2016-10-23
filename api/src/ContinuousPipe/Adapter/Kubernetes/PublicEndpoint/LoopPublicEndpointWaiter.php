<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Promise\PromiseBuilder;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Complex;
use LogStream\Node\Text;
use React;

class LoopPublicEndpointWaiter implements PublicEndpointWaiter
{
    /**
     * The timeout per public endpoint.
     *
     * @var int
     */
    const LOOP_TIMEOUT = 300;

    /**
     * @var int
     */
    const LOOP_INTERVAL = 1;

    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory           $loggerFactory
     * @param SerializerInterface     $serializer
     */
    public function __construct(DeploymentClientFactory $clientFactory, LoggerFactory $loggerFactory, SerializerInterface $serializer)
    {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param DeploymentContext $context
     * @param KubernetesObject  $object
     * @param Log               $log
     *
     * @return React\Promise\PromiseInterface
     *
     * @throws EndpointNotFound
     */
    public function waitEndpoint(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object, Log $log)
    {
        $objectName = $object->getMetadata()->getName();
        $logger = $this->loggerFactory->from($log)->child(new Text('Waiting public endpoint of service '.$objectName));
        $client = $this->clientFactory->get($context);

        $logger->updateStatus(Log::RUNNING);

        return $this->waitPublicEndpoint($loop, $client, $object, $logger)->then(function (PublicEndpoint $endpoint) use ($logger) {
            $logger->updateStatus(Log::SUCCESS);

            return $endpoint;
        }, function (EndpointNotFound $e) use ($logger) {
            $logger->updateStatus(Log::FAILURE);

            throw $e;
        });
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     * @param Logger           $logger
     *
     * @return React\Promise\PromiseInterface
     */
    private function waitPublicEndpoint(React\EventLoop\LoopInterface $loop, NamespaceClient $namespaceClient, KubernetesObject $object, Logger $logger)
    {
        $statusLogger = $logger->child(new Text('No public endpoint found yet.'));

        // Get endpoint status
        $publicEndpointStatusPromise = (new PromiseBuilder($loop))
            ->retry(self::LOOP_INTERVAL, function (React\Promise\Deferred $deferred) use ($namespaceClient, $object, $statusLogger) {
                try {
                    $endpoint = $this->getPublicEndpoint($namespaceClient, $object);

                    $statusLogger->update(new Text('Found endpoint: '.$endpoint->getAddress()));

                    $deferred->resolve($endpoint);
                } catch (EndpointNotFound $e) {
                    $statusLogger->update(new Text($e->getMessage()));
                }
            })
            ->withTimeout(self::LOOP_TIMEOUT)
            ->getPromise()
        ;

        // Get objects' events
        $eventsLogger = $logger->child(new Complex('events'));
        $updateEvents = function () use ($namespaceClient, $object, $eventsLogger) {
            $eventList = $namespaceClient->getEventRepository()->findByObject($object);

            $events = $eventList->getEvents();
            $eventsLogger->update(new Complex('events', [
                'events' => json_decode($this->serializer->serialize($events, 'json'), true),
            ]));
        };

        $timer = $loop->addPeriodicTimer(self::LOOP_INTERVAL, $updateEvents);

        return $publicEndpointStatusPromise->then(function (PublicEndpoint $endpoint) use ($timer, $updateEvents) {
            $timer->cancel();
            $updateEvents();

            return $endpoint;
        }, function ($reason) use ($timer, $updateEvents) {
            $timer->cancel();
            $updateEvents();

            throw $reason;
        });
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    private function getPublicEndpoint(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        $loadBalancer = $this->getLoadBalancerStatus($namespaceClient, $object);
        $name = $object->getMetadata()->getName();

        if (null === $loadBalancer) {
            throw new EndpointNotFound('No load balancer found');
        } elseif (0 === (count($ingresses = $loadBalancer->getIngresses()))) {
            throw new EndpointNotFound('No ingress found');
        }

        foreach ($ingresses as $ingress) {
            if ($hostname = $ingress->getHostname()) {
                return new PublicEndpoint($name, $hostname);
            }

            if ($ip = $ingress->getIp()) {
                return new PublicEndpoint($name, $ip);
            }
        }

        throw new EndpointNotFound('No hostname or IP address found in ingresses');
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return LoadBalancerStatus|null
     *
     * @throws EndpointNotFound
     */
    private function getLoadBalancerStatus(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        $objectName = $object->getMetadata()->getName();

        if ($object instanceof Service) {
            $status = $namespaceClient->getServiceRepository()->findOneByName($objectName)->getStatus();
        } elseif ($object instanceof Ingress) {
            $status = $namespaceClient->getIngressRepository()->findOneByName($objectName)->getStatus();
        } else {
            $status = null;
        }

        if (null === $status) {
            throw new EndpointNotFound('Status not found');
        }

        return $status->getLoadBalancer();
    }
}
