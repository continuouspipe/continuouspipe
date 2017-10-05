<?php

namespace ContinuousPipe\Pipe\Tests\EventBus;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\EventBus\EventStore;
use Ramsey\Uuid\Uuid;

class InMemoryEventStore implements EventStore
{
    /**
     * @var DeploymentEvent[]
     */
    private $events = [];

    /**
     * {@inheritdoc}
     */
    public function add(DeploymentEvent $event)
    {
        $uuid = (string) $event->getDeploymentUuid();
        if (!array_key_exists($uuid, $this->events)) {
            $this->events[$uuid] = [];
        }

        $this->events[$uuid][] = $event;
    }

    /**
     * {@inheritdoc}
     */
    public function findByDeploymentUuid(Uuid $uuid)
    {
        $rawUuid = (string) $uuid;
        if (!array_key_exists($rawUuid, $this->events)) {
            return [];
        }

        return $this->events[$rawUuid];
    }
}
