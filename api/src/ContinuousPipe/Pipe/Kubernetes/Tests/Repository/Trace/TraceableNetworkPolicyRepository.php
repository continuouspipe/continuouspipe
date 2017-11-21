<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\NetworkPolicy\NetworkPolicy;
use Kubernetes\Client\Repository\NetworkPolicyRepository;

class TraceableNetworkPolicyRepository implements NetworkPolicyRepository
{
    /**
     * @var NetworkPolicy[]
     */
    private $updated = [];

    /**
     * @var NetworkPolicy[]
     */
    private $created = [];

    /**
     * @var NetworkPolicyRepository
     */
    private $repository;

    /**
     * @param NetworkPolicyRepository $repository
     */
    public function __construct(NetworkPolicyRepository $repository)
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
    public function update(NetworkPolicy $networkPolicy)
    {
        $updated = $this->repository->update($networkPolicy);

        $this->updated[] = $updated;

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(NetworkPolicy $networkPolicy)
    {
        $created = $this->repository->create($networkPolicy);

        $this->created[] = $created;

        return $created;
    }

    /**
     * @return NetworkPolicy[]
     */
    public function getUpdated(): array
    {
        return $this->updated;
    }

    /**
     * @return NetworkPolicy[]
     */
    public function getCreated(): array
    {
        return $this->created;
    }
}
