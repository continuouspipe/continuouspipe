<?php

namespace ContinuousPipe\River\Infrastructure\InMemory;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\View\TimeResolver;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InMemoryEventStore implements EventStore
{
    /**
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @var TideEventWithMetadata[][]
     */
    private $eventsByTideUuid = [];

    /**
     * @param TimeResolver $timeResolver
     */
    public function __construct(TimeResolver $timeResolver)
    {
        $this->timeResolver = $timeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function add(TideEvent $event)
    {
        $uuid = (string) $event->getTideUuid();
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            $this->eventsByTideUuid[$uuid] = [];
        }

        $this->eventsByTideUuid[$uuid][] = new TideEventWithMetadata($event, $this->timeResolver->resolve());
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(UuidInterface $uuid)
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
    public function findByTideUuidAndTypeWithMetadata(UuidInterface $uuid, $className)
    {
        $uuid = (string) $uuid;
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            return [];
        }

        return array_values(array_filter($this->eventsByTideUuid[$uuid], function (TideEventWithMetadata $eventWithMetadata) use ($className) {
            $event = $eventWithMetadata->getTideEvent();

            return get_class($event) == $className;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByTideUuidWithMetadata(UuidInterface $uuid)
    {
        $uuid = (string) $uuid;
        if (!array_key_exists($uuid, $this->eventsByTideUuid)) {
            return [];
        }

        return $this->eventsByTideUuid[$uuid];
    }
}
