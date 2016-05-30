<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\NamespaceClient;

class ServiceObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getServiceRepository();
    }
}
