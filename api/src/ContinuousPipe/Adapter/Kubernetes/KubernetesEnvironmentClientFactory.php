<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Transformer\ComponentTransformer;
use ContinuousPipe\Adapter\Kubernetes\Transformer\EnvironmentTransformer;
use ContinuousPipe\Adapter\Provider;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;

class KubernetesEnvironmentClientFactory implements EnvironmentClientFactory
{
    /**
     * @var KubernetesClientFactory
     */
    private $clientFactory;

    /**
     * @var ComponentTransformer
     */
    private $componentTransformer;

    /**
     * @param KubernetesClientFactory $clientFactory
     * @param ComponentTransformer $componentTransformer
     */
    public function __construct(KubernetesClientFactory $clientFactory, ComponentTransformer $componentTransformer)
    {
        $this->clientFactory = $clientFactory;
        $this->componentTransformer = $componentTransformer;
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
            $this->componentTransformer
        );
    }
}
