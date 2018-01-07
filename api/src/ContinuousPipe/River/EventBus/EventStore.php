<?php

namespace ContinuousPipe\River\EventBus;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface EventStore
{
    /**
     * @param TideEvent $event
     */
    public function add(TideEvent $event);

    /**
     * @param UuidInterface $uuid
     *
     * @return TideEvent[]
     */
    public function findByTideUuid(UuidInterface $uuid);

    /**
     * @param UuidInterface $uuid
     *
     * @return TideEventWithMetadata[]
     */
    public function findByTideUuidWithMetadata(UuidInterface $uuid);

    /**
     * @param Uuid   $uuid
     * @param string $className
     *
     * @return TideEvent[]
     */
    public function findByTideUuidAndType(Uuid $uuid, $className);

    /**
     * @param Uuid   $uuid
     * @param string $className
     *
     * @return TideEventWithMetadata[]
     */
    public function findByTideUuidAndTypeWithMetadata(UuidInterface $uuid, $className);
}
