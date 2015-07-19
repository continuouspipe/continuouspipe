<?php

namespace ContinuousPipe\River\Infrastructure\InMemory;

use ContinuousPipe\River\Event\TideEvent;
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

        $this->eventsByTideUuid[$uuid][] = $event;
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

        return $this->eventsByTideUuid[$uuid];
    }
}
