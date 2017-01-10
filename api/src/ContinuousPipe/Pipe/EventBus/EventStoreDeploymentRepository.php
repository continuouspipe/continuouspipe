<?php

namespace ContinuousPipe\Pipe\EventBus;

use ContinuousPipe\Pipe\Deployment;
use ContinuousPipe\Pipe\DeploymentRepository;
use Ramsey\Uuid\Uuid;

class EventStoreDeploymentRepository implements DeploymentRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $events = $this->eventStore->findByDeploymentUuid($uuid);

        return Deployment::fromEvents($events);
    }
}
