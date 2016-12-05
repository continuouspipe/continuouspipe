<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\ClusterNotSupported;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Client\KubernetesClientFactory;
use ContinuousPipe\Adapter\Kubernetes\Inspector\NamespaceInspector;
use ContinuousPipe\Security\Credentials\Cluster;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param KubernetesClientFactory $clientFactory
     * @param NamespaceInspector      $namespaceInspector
     * @param LoggerInterface         $logger
     */
    public function __construct(KubernetesClientFactory $clientFactory, NamespaceInspector $namespaceInspector, LoggerInterface $logger)
    {
        $this->clientFactory = $clientFactory;
        $this->namespaceInspector = $namespaceInspector;
        $this->logger = $logger;
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
            $this->namespaceInspector,
            $this->logger
        );
    }
}
