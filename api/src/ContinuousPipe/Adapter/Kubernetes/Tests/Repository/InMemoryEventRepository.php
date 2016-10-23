<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\EventList;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Repository\EventRepository;

class InMemoryEventRepository implements EventRepository
{
    /**
     * {@inheritdoc}
     */
    public function findByObject(KubernetesObject $object)
    {
        return EventList::fromEvents([]);
    }
}
