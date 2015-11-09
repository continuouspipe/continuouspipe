<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\ClusterNotSupported;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
use ContinuousPipe\Security\Credentials\Cluster;

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
    public function getByCluster(Cluster $cluster)
    {
        if (!$cluster instanceof Cluster\Kubernetes) {
            throw new ClusterNotSupported('Only Kubernetes clusters supported');
        }

        return new KubernetesEnvironmentClient(
            $this->clientFactory->getByCluster($cluster),
            $this->namespaceInspector
        );
    }
}
