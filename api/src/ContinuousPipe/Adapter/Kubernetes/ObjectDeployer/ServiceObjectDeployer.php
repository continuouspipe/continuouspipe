<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use ContinuousPipe\Model\Component\DeploymentStrategy;
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

    /**
     * {@inheritdoc}
     */
    protected function needsToBeUpdated(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        if (!parent::needsToBeUpdated($namespaceClient, $object, $deploymentStrategy)) {
            return false;
        }

        /* @var Service $object  */
        $existingService = $this->getRepository($namespaceClient, $object)->findOneByName($object->getMetadata()->getName());
        $existingSelector = $existingService->getSpecification()->getSelector();
        $newSelector = $object->getSpecification()->getSelector();

        return $existingSelector != $newSelector;
    }
}
