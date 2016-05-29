<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\TideFactory;
use Ramsey\Uuid\Uuid;

class EventBusTideRepository implements TideRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @param EventStore  $eventStore
     * @param TideFactory $tideFactory
     */
    public function __construct(EventStore $eventStore, TideFactory $tideFactory)
    {
        $this->eventStore = $eventStore;
        $this->tideFactory = $tideFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $events = $this->eventStore->findByTideUuid($uuid);
        $tide = $this->tideFactory->createFromEvents($events);

        return $tide;
    }
}
