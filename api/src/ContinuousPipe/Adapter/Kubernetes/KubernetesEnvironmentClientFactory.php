<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Adapter\Provider;
use LogStream\LoggerFactory;

class KubernetesEnvironmentClientFactory implements EnvironmentClientFactory
{
    /**
     * @var KubernetesClientFactory
     */
    private $clientFactory;

    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param KubernetesClientFactory $clientFactory
     * @param EnvironmentTransformer $environmentTransformer
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(KubernetesClientFactory $clientFactory, EnvironmentTransformer $environmentTransformer, LoggerFactory $loggerFactory)
    {
        $this->clientFactory = $clientFactory;
        $this->environmentTransformer = $environmentTransformer;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getByProvider(Provider $provider)
    {
        if (!$provider instanceof KubernetesProvider) {
            throw new \RuntimeException('Not supported provider');
        }

        return new KubernetesEnvironmentClient(
            $this->clientFactory->getByProvider($provider),
            $this->environmentTransformer,
            $this->loggerFactory
        );
    }
}
