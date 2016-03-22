<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Repository\NamespaceRepository;

class TraceableNamespaceRepository implements NamespaceRepository
{
    /**
     * @var KubernetesNamespace[]
     */
    private $created = [];

    /**
     * @var KubernetesNamespace[]
     */
    private $deleted = [];

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
    public function findByLabels(KeyValueObjectList $labels)
    {
        return $this->namespaceRepository->findByLabels($labels);
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

        $this->created[] = $created;

        return $created;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesNamespace[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \Kubernetes\Client\Model\KubernetesNamespace[]
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Clear the traces.
     */
    public function clear()
    {
        $this->created = [];
        $this->deleted = [];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(KubernetesNamespace $namespace)
    {
        $deleted = $this->namespaceRepository->delete($namespace);

        $this->deleted[] = $deleted;

        return $deleted;
    }
}
