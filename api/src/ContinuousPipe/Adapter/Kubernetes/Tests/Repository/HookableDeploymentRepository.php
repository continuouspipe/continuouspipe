<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Repository\DeploymentRepository;

class HookableDeploymentRepository implements DeploymentRepository
{
    /**
     * @var DeploymentRepository
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
     * @var callable[]
     */
    private $foundByNameHooks = [];

    /**
     * @param DeploymentRepository $repository
     */
    public function __construct(DeploymentRepository $repository)
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
    public function create(Deployment $deployment)
    {
        $created = $this->repository->create($deployment);

        foreach ($this->createdHooks as $hook) {
            $created = $hook($created);
        }

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Deployment $deployment)
    {
        $updated = $this->repository->update($deployment);

        foreach ($this->updatedHooks as $hook) {
            $updated = $hook($updated);
        }

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        $found = $this->repository->findOneByName($name);

        foreach ($this->foundByNameHooks as $hook) {
            $found = $hook($name, $found);
        }

        return $found;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(Deployment\DeploymentRollback $deploymentRollback)
    {
        return $this->repository->rollback($deploymentRollback);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->repository->exists($name);
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

    /**
     * @param callable $hook
     */
    public function addFoundByNameHook(callable $hook)
    {
        $this->foundByNameHooks[] = $hook;
    }
}
