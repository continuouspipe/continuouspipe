<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class LoopServiceWaiter implements ServiceWaiter
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
     *
     * @throws EndpointNotFound
     *
     * @return PublicEndpoint
     */
    public function waitService(DeploymentContext $context, Service $service)
    {
        $serviceName = $service->getMetadata()->getName();
        $log = $context->getLogger()->append(new Text('Waiting public endpoint of service '.$serviceName));
        $logger = $this->loggerFactory->from($log);
        $client = $this->clientFactory->get($context);

        try {
            $logger->start();

            $endpoint = $this->waitServicePublicEndpoint($client, $service, $logger);

            $logger->append(new Text(sprintf('Found public endpoint "%s": %s', $endpoint->getName(), $endpoint->getAddress())));
            $logger->success();
        } catch (\Exception $e) {
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
     * @throws \Exception
     */
    private function waitServicePublicEndpoint(NamespaceClient $namespaceClient, Service $service, Logger $logger)
    {
        $serviceName = $service->getMetadata()->getName();

        $attempts = 0;
        do {
            try {
                return $this->getServicePublicEndpoint($namespaceClient, $serviceName);
            } catch (\Exception $e) {
                $logger->append(new Text($e->getMessage()));
            }

            sleep(5);
        } while (++$attempts < 10);

        throw new \Exception('Attempted too many times.');
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param string          $serviceName
     *
     * @return PublicEndpoint
     *
     * @throws \Exception
     */
    private function getServicePublicEndpoint(NamespaceClient $namespaceClient, $serviceName)
    {
        $foundService = $namespaceClient->getServiceRepository()->findOneByName($serviceName);

        if ($status = $foundService->getStatus()) {
            if ($loadBalancer = $status->getLoadBalancer()) {
                $ingresses = $loadBalancer->getIngresses();

                if (count($ingresses) > 0) {
                    $ingress = current($ingresses);
                    $ip = $ingress->getIp();

                    if (!empty($ip)) {
                        return new PublicEndpoint($serviceName, $ip);
                    } else {
                        throw new \Exception('Empty IP found');
                    }
                } else {
                    throw new \Exception('No ingress found');
                }
            } else {
                throw new \Exception('No load balancer found');
            }
        }

        throw new \Exception('No status found');
    }
}
