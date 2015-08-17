<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\PodNotFound;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodList;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Repository\PodRepository;

class InMemoryPodRepository implements PodRepository
{
    /**
     * @var Pod[]
     */
    private $pods = [];

    /**
     * @return PodList
     */
    public function findAll()
    {
        return PodList::fromPods($this->pods);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function create(Pod $pod)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function update(Pod $pod)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Pod $pod)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findByReplicationController(ReplicationController $replicationController)
    {
        throw new \RuntimeException('Not implemented yet');
    }
}