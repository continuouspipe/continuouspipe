<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
use ContinuousPipe\Adapter\Provider;

class KubernetesEnvironmentClientFactory implements EnvironmentClientFactory
{
    /**
     * @var KubernetesClientFactory
     */
    private $clientFactory;

    /**
     * @var NamespaceInspector
     */
    private $namespaceInspector;

    /**
     * @param KubernetesClientFactory $clientFactory
     * @param NamespaceInspector      $namespaceInspector
     */
    public function __construct(KubernetesClientFactory $clientFactory, NamespaceInspector $namespaceInspector)
    {
        $this->clientFactory = $clientFactory;
        $this->namespaceInspector = $namespaceInspector;
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
            $this->namespaceInspector
        );
    }
}
