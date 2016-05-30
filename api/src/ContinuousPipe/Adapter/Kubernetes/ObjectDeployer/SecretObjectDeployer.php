<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\NamespaceClient;

class SecretObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Secret;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getSecretRepository();
    }
}
