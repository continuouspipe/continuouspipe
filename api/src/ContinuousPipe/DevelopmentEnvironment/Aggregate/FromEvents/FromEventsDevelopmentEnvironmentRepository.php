<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\FromEvents;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironmentRepository;
use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironment;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironmentNotFound;
use ContinuousPipe\Events\EventStore\EventStore;
use Ramsey\Uuid\UuidInterface;

class FromEventsDevelopmentEnvironmentRepository implements DevelopmentEnvironmentRepository
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
    public function find(UuidInterface $uuid): DevelopmentEnvironment
    {
        $events = $this->eventStore->read((string) EventStream::fromDevelopmentEnvironmentIdentifier($uuid));

        if (0 === count($events)) {
            throw new DevelopmentEnvironmentNotFound(sprintf(
                'Development environment "%s" is not found',
                $uuid->toString()
            ));
        }

        return DevelopmentEnvironment::fromEvents($events);
    }
}
