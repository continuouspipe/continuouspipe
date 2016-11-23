<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Repository\ServiceRepository;

class TraceableServiceRepository implements ServiceRepository
{
    /**
     * @var ServiceRepository
     */
    private $repository;

    /**
     * @var Service[]
     */
    private $created = [];

    /**
     * @var Service[]
     */
    private $updated = [];

    /**
     * @var Service[]
     */
    private $deleted = [];

    /**
     * @param ServiceRepository $repository
     */
    public function __construct(ServiceRepository $repository)
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
    public function create(Service $service)
    {
        $created = $this->repository->create($service);

        $this->created[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Service $service)
    {
        $this->repository->delete($service);

        $this->deleted[] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Service $service)
    {
        $updated = $this->repository->update($service);

        $this->updated[] = $updated;

        return $updated;
    }

    /**
     * @return \Kubernetes\Client\Model\Service[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \Kubernetes\Client\Model\Service[]
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return \Kubernetes\Client\Model\Service[]
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Clear traces.
     */
    public function clear()
    {
        $this->created = [];
        $this->updated = [];
        $this->deleted = [];
    }
}
