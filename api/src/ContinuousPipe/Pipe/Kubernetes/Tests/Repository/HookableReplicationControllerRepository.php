<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository;

use GuzzleHttp\Promise\PromiseInterface;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\ReplicationControllerRepository;

class HookableReplicationControllerRepository implements ReplicationControllerRepository
{
    /**
     * @var ReplicationControllerRepository
     */
    private $repository;

    /**
     * @var callable[]
     */
    private $createdHooks = [];

    /**
     * @var callable[]
     */
    private $updatedHooks = [];

    /**
     * @param ReplicationControllerRepository $repository
     */
    public function __construct(ReplicationControllerRepository $repository)
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
    public function asyncFindAll()
    {
        return $this->repository->asyncFindAll();
    }

    /**
     * {@inheritdoc}
     */
    public function create(ReplicationController $replicationController)
    {
        $created = $this->repository->create($replicationController);

        foreach ($this->createdHooks as $hook) {
            $created = $hook($created);
        }

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ReplicationController $replicationController)
    {
        $updated = $this->repository->update($replicationController);

        foreach ($this->updatedHooks as $hook) {
            $updated = $hook($updated);
        }

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReplicationController $replicationController)
    {
        return $this->repository->delete($replicationController);
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
    public function findOneByLabels(array $labels)
    {
        return $this->repository->findOneByLabels($labels);
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
    public function findByLabels(array $labels)
    {
        return $this->repository->findByLabels($labels);
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
    public function addUpdatedHook(callable $hook)
    {
        $this->updatedHooks[] = $hook;
    }
}
