<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Service\Service;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\NamespaceClient;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class LoopServiceWaiter implements ServiceWaiter
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
     * @param DeploymentClientFactory $clientFactory
     * @param LoggerFactory           $loggerFactory
     */
    public function __construct(DeploymentClientFactory $clientFactory, LoggerFactory $loggerFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param DeploymentContext $context
     * @param Service           $service
     * @param Log               $log
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    public function waitService(DeploymentContext $context, Service $service, Log $log)
    {
        $serviceName = $service->getService()->getMetadata()->getName();
        $logger = $this->loggerFactory->from($log)->child(new Text('Waiting public endpoint of service '.$serviceName));
        $client = $this->clientFactory->get($context);

        try {
            $logger->updateStatus(Log::RUNNING);

            $endpoint = $this->waitServicePublicEndpoint($client, $service, $logger);

            $logger->child(
                new Text(sprintf('Found public endpoint "%s": %s', $endpoint->getName(), $endpoint->getAddress()))
            );
            $logger->updateStatus(Log::SUCCESS);
        } catch (EndpointNotFound $e) {
            $logger->child(new Text($e->getMessage()));
            $logger->updateStatus(Log::FAILURE);

            throw new EndpointNotFound($e->getMessage(), $e->getCode(), $e);
        }

        return $endpoint;
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param Service         $service
     * @param Logger          $logger
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    private function waitServicePublicEndpoint(NamespaceClient $namespaceClient, Service $service, Logger $logger)
    {
        $serviceName = $service->getService()->getMetadata()->getName();

        $attempts = 0;
        do {
            try {
                return $this->getServicePublicEndpoint($namespaceClient, $serviceName);
            } catch (EndpointNotFound $e) {
                $logger->child(new Text($e->getMessage()));
            }

            // FIXME Replace with Tolerance's `Waiter`
            sleep(self::LOOP_WAIT);
        } while (++$attempts < self::LOOP_MAX_RETRY);

        throw new EndpointNotFound('Attempted too many times.');
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param string          $serviceName
     *
     * @return PublicEndpoint
     *
     * @throws EndpointNotFound
     */
    private function getServicePublicEndpoint(NamespaceClient $namespaceClient, $serviceName)
    {
        $foundService = $namespaceClient->getServiceRepository()->findOneByName($serviceName);

        if (null === ($status = $foundService->getStatus())) {
            throw new EndpointNotFound('No service status found');
        } elseif (null === ($loadBalancer = $status->getLoadBalancer())) {
            throw new EndpointNotFound('No load balancer found');
        } elseif (0 === (count($ingresses = $loadBalancer->getIngresses()))) {
            throw new EndpointNotFound('No ingress found');
        }

        foreach ($ingresses as $ingress) {
            if ($hostname = $ingress->getHostname()) {
                return new PublicEndpoint($serviceName, $hostname);
            }

            if ($ip = $ingress->getIp()) {
                return new PublicEndpoint($serviceName, $ip);
            }
        }

        throw new EndpointNotFound('No hostname or IP address found in ingresses');
    }
}
