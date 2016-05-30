<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Model\Component\DeploymentStrategy;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;
use Kubernetes\Client\Repository\WrappedObjectRepository;

abstract class AbstractObjectDeployer implements ObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function deploy(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        $repository = $this->getRepository($namespaceClient, $object);
        $name = $object->getMetadata()->getName();
        $created = [];
        $updated = [];

        if ($repository->exists($name)) {
            if (null === $deploymentStrategy || $deploymentStrategy->isLocked() === false) {
                $updated[] = $this->update($namespaceClient, $object);
            }
        } else {
            $created[] = $this->create($namespaceClient, $object);
        }

        return new ComponentCreationStatus($created, $updated);
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return WrappedObjectRepository
     */
    abstract protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object);

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return KubernetesObject
     */
    protected function update(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $this->getRepository($namespaceClient, $object)->update($object);
    }

    /**
     * @param NamespaceClient  $namespaceClient
     * @param KubernetesObject $object
     *
     * @return KubernetesObject
     */
    protected function create(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $this->getRepository($namespaceClient, $object)->create($object);
    }
}
