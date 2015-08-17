<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\ServiceAccount;
use Kubernetes\Client\Repository\ServiceAccountRepository;

class TraceableServiceAccountRepository implements ServiceAccountRepository
{
    /**
     * @var ServiceAccount[]
     */
    private $updated = [];

    /**
     * @var ServiceAccountRepository
     */
    private $repository;

    /**
     * @param ServiceAccountRepository $repository
     */
    public function __construct(ServiceAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        return $this->repository->findByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function update(ServiceAccount $serviceAccount)
    {
        $updated = $this->repository->update($serviceAccount);

        $this->updated[] = $updated;

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ServiceAccount $serviceAccount)
    {
        return $this->repository->create($serviceAccount);
    }

    /**
     * @return \Kubernetes\Client\Model\ServiceAccount[]
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
