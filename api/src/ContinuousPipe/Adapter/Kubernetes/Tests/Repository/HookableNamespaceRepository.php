<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Repository\NamespaceRepository;

class HookableNamespaceRepository implements NamespaceRepository
{
    /**
     * @var NamespaceRepository
     */
    private $decoratedRepository;

    /**
     * @var callable[]
     */
    private $deleteHooks = [];

    /**
     * @param NamespaceRepository $decoratedRepository
     */
    public function __construct(NamespaceRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->decoratedRepository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(KeyValueObjectList $labels)
    {
        return $this->decoratedRepository->findByLabels($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        return $this->decoratedRepository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->decoratedRepository->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function create(KubernetesNamespace $namespace)
    {
        return $this->decoratedRepository->create($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(KubernetesNamespace $namespace)
    {
        $result = $this->decoratedRepository->delete($namespace);

        foreach ($this->deleteHooks as $hook) {
            $result = $hook($result);
        }

        return $result;
    }

    /**
     * @param callable $hook
     */
    public function addDeleteHooks(callable $hook)
    {
        $this->deleteHooks[] = $hook;
    }
}
