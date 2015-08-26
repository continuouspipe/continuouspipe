<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\NamespaceNotFound;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\NamespaceList;
use Kubernetes\Client\Repository\NamespaceRepository;

class InMemoryNamespaceRepository implements NamespaceRepository
{
    private $namespaces = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return new NamespaceList($this->namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->namespaces)) {
            throw new NamespaceNotFound();
        }

        return $this->namespaces[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->namespaces);
    }

    /**
     * {@inheritdoc}
     */
    public function create(KubernetesNamespace $namespace)
    {
        $this->namespaces[$namespace->getMetadata()->getName()] = $namespace;

        return $namespace;
    }
}
