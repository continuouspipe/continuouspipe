<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\PersistentVolumeClaim;
use Kubernetes\Client\Repository\PersistentVolumeClaimRepository;

class TraceablePersistentVolumeClaimRepository implements PersistentVolumeClaimRepository
{
    /**
     * @var PersistentVolumeClaimRepository
     */
    private $repository;

    /**
     * @var PersistentVolumeClaim[]
     */
    private $created = [];

    /**
     * @param PersistentVolumeClaimRepository $repository
     */
    public function __construct(PersistentVolumeClaimRepository $repository)
    {
        $this->repository = $repository;
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
    public function create(PersistentVolumeClaim $claim)
    {
        $created = $this->repository->create($claim);

        $this->created[] = $created;

        return $created;
    }

    /**
     * @return \Kubernetes\Client\Model\PersistentVolumeClaim[]
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Clear traces.
     */
    public function clear()
    {
        $this->created = [];
    }
}
