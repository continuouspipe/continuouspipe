<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;

class DeploymentObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Deployment;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getDeploymentRepository();
    }
}
