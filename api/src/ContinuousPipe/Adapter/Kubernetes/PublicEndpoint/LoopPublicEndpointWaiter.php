<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\LoadBalancerStatus;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Tolerance\Waiter\Waiter;

class LoopPublicEndpointWaiter implements PublicEndpointWaiter
{
    /**
     * Number of second between each loop.
     *
     * @var int
     */
    const LOOP_WAIT = 10;

    /**
     * Number of maximum tries to wait a public endpoint.
     *
     * @var int
     */
    const LOOP_MAX_RETRY = 30;

    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var Waiter
     */
    private $waiter;

    /**
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory           $loggerFactory
     * @param Waiter                  $waiter
     */
    public function __construct(DeploymentClientFactory $clientFactory, LoggerFactory $loggerFactory, Waiter $waiter)
    {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
        $this->waiter = $waiter;
    }

    /**
     * @param DeploymentContext $context
     * @param KubernetesObject  $object
     * @param Log               $log
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    public function waitEndpoint(DeploymentContext $context, KubernetesObject $object, Log $log)
    {
        $objectName = $object->getMetadata()->getName();
        $logger = $this->loggerFactory->from($log)->child(new Text('Waiting public endpoint of service '.$objectName));
        $client = $this->clientFactory->get($context);

        try {
            $logger->updateStatus(Log::RUNNING);

            $endpoint = $this->waitPublicEndpoint($client, $object, $logger);

            $logger->child(new Text(sprintf('Found public endpoint "%s": %s', $endpoint->getName(), $endpoint->getAddress())));
            $logger->updateStatus(Log::SUCCESS);
        } catch (EndpointNotFound $e) {
            $logger->child(new Text($e->getMessage()));
            $logger->updateStatus(Log::FAILURE);

            throw new EndpointNotFound($e->getMessage(), $e->getCode(), $e);
        }

        return $endpoint;
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     * @param Logger           $logger
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    private function waitPublicEndpoint(NamespaceClient $namespaceClient, KubernetesObject $object, Logger $logger)
    {
        $attempts = 0;
        do {
            try {
                return $this->getPublicEndpoint($namespaceClient, $object);
            } catch (EndpointNotFound $e) {
                $logger->child(new Text($e->getMessage()));
            }

            $this->waiter->wait(self::LOOP_WAIT);
        } while (++$attempts < self::LOOP_MAX_RETRY);

        throw new EndpointNotFound('Attempted too many times.');
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
