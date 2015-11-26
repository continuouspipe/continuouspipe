<?php

namespace ContinuousPipe\River\Infrastructure\InMemory;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use ContinuousPipe\River\EventBus\EventStore;
use Rhumsaa\Uuid\Uuid;

class InMemoryEventStore implements EventStore
{
    /**
     * @var array
     */
    private $eventsByTideUuid = [];

    /**
     * {@inheritdoc}
     */
    public function add(TideEvent $event)
    {
        $uuid = (string) $event->getTideUuid();
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            $this->eventsByTideUuid[$uuid] = [];
        }

        $this->eventsByTideUuid[$uuid][] = new TideEventWithMetadata($event, new \DateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(Uuid $uuid)
    {
        $uuid = (string) $uuid;
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            return [];
        }

        return array_map(function (TideEventWithMetadata $eventWithMetadata) {
            return $eventWithMetadata->getTideEvent();
        }, $this->eventsByTideUuid[$uuid]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidAndType(Uuid $uuid, $className)
    {
        return array_values(array_filter($this->findByTideUuid($uuid), function ($event) use ($className) {
            return get_class($event) == $className;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidAndTypeWithMetadata(Uuid $uuid, $className)
    {
        $uuid = (string) $uuid;
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            return [];
        }

        return array_values(array_filter($this->eventsByTideUuid[$uuid], function ($event) use ($className) {
            return get_class($event) == $className;
        }));
    }
}
