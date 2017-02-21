<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use ContinuousPipe\Pipe\Environment\PublicEndpointPort;
use ContinuousPipe\Pipe\Promise\PromiseBuilder;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
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
     * @var int
     */
    private $endpointTimeout;
    /**
     * @var int
     */
    private $endpointInterval;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory           $loggerFactory
     * @param SerializerInterface     $serializer
     * @param int                     $endpointTimeout
     * @param int                     $endpointInterval
     */
    public function __construct(DeploymentClientFactory $clientFactory, LoggerFactory $loggerFactory, SerializerInterface $serializer, int $endpointTimeout, int $endpointInterval)
    {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
        $this->serializer = $serializer;
        $this->endpointTimeout = $endpointTimeout;
        $this->endpointInterval = $endpointInterval;
    }

    /**
     * @param DeploymentContext $context
     * @param KubernetesObject  $object
     *
     * @return React\Promise\PromiseInterface
     *
     * @throws EndpointNotFound
     */
    public function waitEndpoint(React\EventLoop\LoopInterface $loop, DeploymentContext $context, KubernetesObject $object)
    {
        $objectName = $object->getMetadata()->getName();
        $logger = $this->loggerFactory->from($context->getLog())->child(new Text('Waiting public endpoint of service '.$objectName));
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
            ->retry($this->endpointInterval, function (React\Promise\Deferred $deferred) use ($namespaceClient, $object, $statusLogger) {
                try {
                    $endpoint = $this->getPublicEndpoint($namespaceClient, $object);

                    $statusLogger->update(new Text('Found endpoint: '.$endpoint->getAddress()));

                    $deferred->resolve($endpoint);
                } catch (EndpointNotFound $e) {
                    $statusLogger->update(new Text($e->getMessage()));
                }
            })
            ->withTimeout($this->endpointTimeout)
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

        $timer = $loop->addPeriodicTimer($this->endpointInterval, $updateEvents);

        return $publicEndpointStatusPromise->then(function (PublicEndpoint $endpoint) use ($timer, $updateEvents) {
            $timer->cancel();
            $updateEvents();

            return $endpoint;
        }, function ($reason) use ($timer, $updateEvents) {
            $timer->cancel();
            $updateEvents();

            if ($reason instanceof React\Promise\Timer\TimeoutException) {
                $reason = new EndpointNotFound('Endpoint still not found. Timed-out.');
            }

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

        $ports = $this->getPorts($object);

        foreach ($ingresses as $ingress) {
            if ($hostname = $ingress->getHostname()) {
                return new PublicEndpoint($name, $hostname, $ports);
            }

            if ($ip = $ingress->getIp()) {
                return new PublicEndpoint($name, $ip, $ports);
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

    /**
     * @param KubernetesObject $object
     *
     * @return array
     */
    private function getPorts(KubernetesObject $object)
    {
        if ($object instanceof Service) {
            return array_map(function (ServicePort $servicePort) {
                return new PublicEndpointPort(
                    $servicePort->getPort(),
                    $servicePort->getProtocol()
                );
            }, $object->getSpecification()->getPorts());
        } elseif ($object instanceof Ingress) {
            return [
                new PublicEndpointPort(
                    $object->getSpecification()->getBackend()->getServicePort(),
                    PublicEndpointPort::PROTOCOL_TCP
                ),
            ];
        }

        throw new EndpointNotFound('Unable to get the exposed ports from the '.get_class($object));
    }
}
