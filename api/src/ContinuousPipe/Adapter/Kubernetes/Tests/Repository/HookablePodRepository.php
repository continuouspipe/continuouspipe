<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\PodRepository;

class HookablePodRepository implements PodRepository
{
    /**
     * @var PodRepository
     */
    private $repository;

    /**
     * @var callable[]
     */
    private $foundByReplicationControllerHooks = [];

    /**
     * @param PodRepository $repository
     */
    public function __construct(PodRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        return $this->repository->findByLabels($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Pod $pod)
    {
        return $this->repository->create($pod);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Pod $pod)
    {
        return $this->repository->update($pod);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        return $this->repository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->repository->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pod $pod)
    {
        return $this->repository->delete($pod);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReplicationController(ReplicationController $replicationController)
    {
        $found = $this->repository->findByReplicationController($replicationController);

        foreach ($this->foundByReplicationControllerHooks as $hook) {
            $found = $hook($replicationController, $found);
        }

        return $found;
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Pod $pod, callable $callable)
    {
        return $this->repository->attach($pod, $callable);
    }

    /**
     * @param callable $hook
     */
    public function addFoundByReplicationControllerHook(callable $hook)
    {
        $this->foundByReplicationControllerHooks[] = $hook;
    }
}
