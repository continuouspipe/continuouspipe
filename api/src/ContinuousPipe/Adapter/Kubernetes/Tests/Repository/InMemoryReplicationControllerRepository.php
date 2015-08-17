<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Exception\TooManyObjects;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\ReplicationControllerList;
use Kubernetes\Client\Repository\ReplicationControllerRepository;

class InMemoryReplicationControllerRepository implements ReplicationControllerRepository
{

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function create(ReplicationController $replicationController)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function update(ReplicationController $replicationController)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReplicationController $replicationController)
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
    public function findOneByLabels(array $labels)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        throw new \RuntimeException('Not implemented yet');
    }
}