<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use GuzzleHttp\Promise\PromiseInterface;
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
     * @var callable[]
     */
    private $foundByLabelsHooks = [];

    /**
     * @var callable[]
     */
    private $createdHooks = [];

    /**
     * @var callable[]
     */
    private $deletedHooks = [];

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
    public function asyncFindAll() : PromiseInterface
    {
        return $this->repository->asyncFindAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        $found = $this->repository->findByLabels($labels);

        foreach ($this->foundByLabelsHooks as $hook) {
            $found = $hook($labels, $found);
        }

        return $found;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Pod $pod)
    {
        $created = $this->repository->create($pod);

        foreach ($this->createdHooks as $hook) {
            $created = $hook($created);
        }

        return $created;
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
        $deleted = $this->repository->delete($pod);

        foreach ($this->deletedHooks as $hook) {
            $deleted = $hook($pod);
        }

        return $deleted;
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

    /**
     * @param callable $hook
     */
    public function addCreatedHook(callable $hook)
    {
        $this->createdHooks[] = $hook;
    }

    /**
     * @param callable $hook
     */
    public function addDeletedHook(callable $hook)
    {
        $this->deletedHooks[] = $hook;
    }

    /**
     * @param callable $hook
     */
    public function addFoundByLabelsHook(callable $hook)
    {
        $this->foundByLabelsHooks[] = $hook;
    }
}
