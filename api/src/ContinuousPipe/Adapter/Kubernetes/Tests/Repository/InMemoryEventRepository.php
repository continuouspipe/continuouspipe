<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Event;
use Kubernetes\Client\Model\EventList;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Repository\EventRepository;

class InMemoryEventRepository implements EventRepository
{
    private $objectEvents = [];

    /**
     * {@inheritdoc}
     */
    public function findByObject(KubernetesObject $object)
    {
        $events = [];

        $objectName = $object->getMetadata()->getName();

        if (isset($this->objectEvents[$objectName])) {
            $events = $this->objectEvents[$objectName];
        }

        return EventList::fromEvents($events);
    }

    public function addObjectEvent(Event $event)
    {
        $objectName = $event->getMetadata()->getName();

        if (!isset($this->objectEvents[$objectName])) {
            $this->objectEvents[$objectName] = [];
        }

        $this->objectEvents[$objectName][] = $event;
    }
}
