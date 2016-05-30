<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\ReplicationControllerRepository;

class TraceableReplicationControllerRepository implements ReplicationControllerRepository
{
    /**
     * @var ReplicationController[]
     */
    private $updatedReplicationControllers = [];

    /**
     * @var ReplicationController[]
     */
    private $createdReplicationControllers = [];

    /**
     * @var ReplicationController[]
     */
    private $deletedReplicationControllers = [];

    /**
     * @var ReplicationControllerRepository
     */
    private $repository;

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
    public function create(ReplicationController $replicationController)
    {
        $created = $this->repository->create($replicationController);

        $this->createdReplicationControllers[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ReplicationController $replicationController)
    {
        $updated = $this->repository->update($replicationController);

        $this->updatedReplicationControllers[] = $updated;

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReplicationController $replicationController)
    {
        $this->repository->delete($replicationController);

        $this->deletedReplicationControllers[] = $replicationController;
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
    public function findByLabels(array $labels)
    {
        return $this->repository->findByLabels($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->repository->exists($name);
    }

    /**
     * @return \Kubernetes\Client\Model\ReplicationController[]
     */
    public function getUpdatedReplicationControllers()
    {
        return $this->updatedReplicationControllers;
    }

    /**
     * @return \Kubernetes\Client\Model\ReplicationController[]
     */
    public function getCreatedReplicationControllers()
    {
        return $this->createdReplicationControllers;
    }

    /**
     * @return \Kubernetes\Client\Model\ReplicationController[]
     */
    public function getDeletedReplicationControllers()
    {
        return $this->deletedReplicationControllers;
    }
}
