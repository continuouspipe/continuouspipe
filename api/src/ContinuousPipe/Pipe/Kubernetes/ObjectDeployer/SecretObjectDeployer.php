<?php

namespace ContinuousPipe\Pipe\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\NamespaceClient;
use ContinuousPipe\Model\Component\DeploymentStrategy;

class SecretObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    protected function needsToBeUpdated(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        if (!parent::needsToBeUpdated($namespaceClient, $object, $deploymentStrategy)) {
            return false;
        }

        /* @var Secret $object  */
        /* @var Secret $existingSecret */
        $existingSecret = $this->getRepository($namespaceClient, $object)->findOneByName($object->getMetadata()->getName());

        return
            // Updates if the selector changed
            $existingSecret->getData() != $object->getData()
        ;
    }

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
