<?php

namespace ContinuousPipe\River\EventBus;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

interface EventStore
{
    /**
     * @param TideEvent $event
     */
    public function add(TideEvent $event);

    /**
     * @param Uuid $uuid
     *
     * @return TideEvent[]
     */
    public function findByTideUuid(Uuid $uuid);

    /**
     * @param Uuid   $uuid
     * @param string $className
     *
     * @return TideEvent[]
     */
    public function findByTideUuidAndType(Uuid $uuid, $className);
}
