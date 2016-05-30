<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\NamespaceClient;

class PodObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Pod;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getPodRepository();
    }
}
