<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Repository\NamespaceRepository;

class TraceableNamespaceRepository implements NamespaceRepository
{
    /**
     * @var NamespaceRepository[]
     */
    private $createdRepositories = [];

    /**
     * @var NamespaceRepository
     */
    private $namespaceRepository;

    /**
     * @param NamespaceRepository $namespaceRepository
     */
    public function __construct(NamespaceRepository $namespaceRepository)
    {
        $this->namespaceRepository = $namespaceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->namespaceRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        return $this->namespaceRepository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->namespaceRepository->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function create(KubernetesNamespace $namespace)
    {
        $created = $this->namespaceRepository->create($namespace);

        $this->createdRepositories[] = $created;

        return $created;
    }

    /**
     * @return \Kubernetes\Client\Repository\NamespaceRepository[]
     */
    public function getCreatedRepositories()
    {
        return $this->createdRepositories;
    }

    /**
     * Clear the traces.
     */
    public function clear()
    {
        $this->createdRepositories = [];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(KubernetesNamespace $namespace)
    {
        return $this->namespaceRepository->delete($namespace);
    }
}
