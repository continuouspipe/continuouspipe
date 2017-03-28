<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use GuzzleHttp\Promise\FulfilledPromise;
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
    public function asyncFindAll()
    {
        return new FulfilledPromise(ReplicationControllerList::fromReplicationControllers($this->replicationControllers));
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
        $replicationControllers = $this->findByLabels($labels);
        if (count($replicationControllers) === 0) {
            throw new ReplicationControllerNotFound('No matching replication controller found');
        }

        return current($replicationControllers);
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels)
    {
        return array_filter($this->replicationControllers, function (ReplicationController $replicationController) use ($labels) {
            return $this->isMatchingLabels($replicationController, $labels);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->replicationControllers);
    }

    /**
     * Return true is the replication controller labels' are matching.
     *
     * @param ReplicationController $replicationController
     * @param array                 $labels
     *
     * @return bool
     */
    private function isMatchingLabels(ReplicationController $replicationController, array $labels)
    {
        $replicationControllerLabels = $replicationController->getMetadata()->getLabelsAsAssociativeArray();

        foreach ($labels as $key => $value) {
            if (!array_key_exists($key, $replicationControllerLabels)) {
                return false;
            } elseif ($replicationControllerLabels[$key] != $labels[$key]) {
                return false;
            }
        }

        return true;
    }
}
