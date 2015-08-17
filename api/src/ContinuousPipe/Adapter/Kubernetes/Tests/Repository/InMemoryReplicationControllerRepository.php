<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\ReplicationControllerList;
use Kubernetes\Client\Repository\ReplicationControllerRepository;

class InMemoryReplicationControllerRepository implements ReplicationControllerRepository
{
    /**
     * @var ReplicationController[]
     */
    private $replicationControllers = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return ReplicationControllerList::fromReplicationControllers($this->replicationControllers);
    }

    /**
     * {@inheritdoc}
     */
    public function create(ReplicationController $replicationController)
    {
        $this->replicationControllers[$replicationController->getMetadata()->getName()] = $replicationController;

        return $replicationController;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ReplicationController $replicationController)
    {
        $this->replicationControllers[$replicationController->getMetadata()->getName()] = $replicationController;

        return $replicationController;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ReplicationController $replicationController)
    {
        $name = $replicationController->getMetadata()->getName();
        if (!array_key_exists($name, $this->replicationControllers)) {
            throw new ReplicationControllerNotFound();
        }

        unset($this->replicationControllers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->replicationControllers)) {
            throw new ReplicationControllerNotFound();
        }

        return $this->replicationControllers[$name];
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