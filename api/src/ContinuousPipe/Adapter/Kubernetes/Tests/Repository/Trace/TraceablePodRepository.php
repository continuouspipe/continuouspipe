<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\PodRepository;

class TraceablePodRepository implements PodRepository
{
    /**
     * @var PodRepository
     */
    private $repository;

    /**
     * @var Pod[]
     */
    private $created = [];

    /**
     * @var Pod[]
     */
    private $deleted = [];

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
    public function create(Pod $pod)
    {
        $created = $this->repository->create($pod);

        $this->created[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pod $pod)
    {
        $this->repository->delete($pod);

        $this->deleted[] = $pod;
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
    public function exists($name)
    {
        return $this->repository->exists($name);
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
    public function findOneByName($name)
    {
        return $this->repository->findOneByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReplicationController(ReplicationController $replicationController)
    {
        return $this->repository->findByReplicationController($replicationController);
    }

    /**
     * {@inheritdoc}
     */
    public function attach(Pod $pod, callable $callable)
    {
        return $this->repository->attach($pod, $callable);
    }

    /**
     * @return \Kubernetes\Client\Model\Pod[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \Kubernetes\Client\Model\Pod[]
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
