<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository;

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
     * @var callable[]
     */
    private $findAllHooks = [];

    /**
     * @var callable[]
     */
    private $findByLabelsHooks = [];

    /**
     * @var callable[]
     */
    private $findOneByNameHooks = [];

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
        $result = $this->decoratedRepository->findAll();

        return $this->executeHooks($this->findAllHooks, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(KeyValueObjectList $labels)
    {
        $result = $this->decoratedRepository->findByLabels($labels);

        return $this->executeHooks($this->findByLabelsHooks, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        $result = $this->decoratedRepository->findOneByName($name);

        return $this->executeHooks($this->findOneByNameHooks, $result);
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

        return $this->executeHooks($this->deleteHooks, $result);
    }

    /**
     * @param callable $hook
     */
    public function addDeleteHooks(callable $hook)
    {
        $this->deleteHooks[] = $hook;
    }

    public function addFindAllHook(callable $hook)
    {
        $this->findAllHooks[] = $hook;
    }

    public function addFindByLabelsHook(callable $hook)
    {
        $this->findByLabelsHooks[] = $hook;
    }

    public function addFindOneByNameHook(callable $hook)
    {
        $this->findOneByNameHooks[] = $hook;
    }

    private function executeHooks($hooks, $input)
    {
        $result = $input;
        foreach ($hooks as $hook) {
            $result = $hook($result);
        }
        return $result;
    }
}
