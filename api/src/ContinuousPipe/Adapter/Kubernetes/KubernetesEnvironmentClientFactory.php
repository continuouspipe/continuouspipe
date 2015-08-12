<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Adapter\Provider;

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
     * @param KubernetesClientFactory $clientFactory
     */
    public function __construct(KubernetesClientFactory $clientFactory, EnvironmentTransformer $environmentTransformer)
    {
        $this->clientFactory = $clientFactory;
        $this->environmentTransformer = $environmentTransformer;
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
            $this->environmentTransformer
        );
    }
}
