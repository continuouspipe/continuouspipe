<?php

namespace ContinuousPipe\River;

use ContinuousPipe\Builder\Repository;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideStarted;
use Rhumsaa\Uuid\Uuid;

class Tide
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var TideEvent[]
     */
    private $events;

    /**
     * @var TideEvent[]
     */
    private $newEvents;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Uuid $uuid
     */
    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
        $this->events = [];
        $this->newEvents = [];
    }

    /**
     * Create a new tide.
     *
     * @return Tide
     */
    public static function createFromRepository(Repository $repository)
    {
        $uuid = Uuid::uuid1();
        $tide = new self($uuid);
        $tide->apply(new TideStarted($uuid, $repository));

        return $tide;
    }

    /**
     * Apply a given event.
     *
     * @param TideEvent $event
     */
    public function apply(TideEvent $event)
    {
        if ($event instanceof TideStarted) {
            $this->repository = $event->getRepository();
        }

        $this->newEvents[] = $event;
        $this->events[] = $event;
    }

    /**
     * @return TideEvent[]
     */
    public function popNewEvents()
    {
        $events = $this->newEvents;
        $this->newEvents = [];
        return $events;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Repository
     */
    public function getCodeRepository()
    {
        return $this->repository;
    }
}
