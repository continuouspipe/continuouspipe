<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use ContinuousPipe\Adapter\Kubernetes\KubernetesDeploymentContext;
use ContinuousPipe\Pipe\DeploymentContext;

class DeploymentClientFactory
{
    /**
     * @var KubernetesClientFactory
     */
    private $kubernetesClientFactory;

    /**
     * @param KubernetesClientFactory $kubernetesClientFactory
     */
    public function __construct(KubernetesClientFactory $kubernetesClientFactory)
    {
        $this->kubernetesClientFactory = $kubernetesClientFactory;
    }

    /**
     * @param DeploymentContext $context
     *
     * @return \Kubernetes\Client\NamespaceClient
     */
    public function get(DeploymentContext $context)
    {
        $client = $this->kubernetesClientFactory->getByCluster($context->getCluster());

        if (!$context->has(KubernetesDeploymentContext::NAMESPACE_KEY)) {
            throw new \RuntimeException('Expecting the namespace object to be in the deployment context');
        }

        $namespace = $context->get(KubernetesDeploymentContext::NAMESPACE_KEY);
        $namespaceClient = $client->getNamespaceClient($namespace);

        return $namespaceClient;
    }
}
