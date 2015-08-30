<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
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
        $serviceName = $service->getMetadata()->getName();
        $log = $this->loggerFactory->from($log)->append(new Text('Waiting public endpoint of service '.$serviceName));
        $logger = $this->loggerFactory->from($log);
        $client = $this->clientFactory->get($context);

        try {
            $logger->start();

            $endpoint = $this->waitServicePublicEndpoint($client, $service, $logger);

            $logger->append(new Text(sprintf('Found public endpoint "%s": %s', $endpoint->getName(), $endpoint->getAddress())));
            $logger->success();
        } catch (EndpointNotFound $e) {
            $logger->append(new Text($e->getMessage()));
            $logger->failure();

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
        $serviceName = $service->getMetadata()->getName();

        $attempts = 0;
        do {
            try {
                return $this->getServicePublicEndpoint($namespaceClient, $serviceName);
            } catch (EndpointNotFound $e) {
                $logger->append(new Text($e->getMessage()));
            }

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

        $ingress = current($ingresses);
        $ip = $ingress->getIp();

        if (empty($ip)) {
            throw new EndpointNotFound('Empty IP found');
        }

        return new PublicEndpoint($serviceName, $ip);
    }
}
