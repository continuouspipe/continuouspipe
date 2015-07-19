<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\User\User;
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
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var User
     */
    private $user;

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
     * @param Flow $flow
     * @param CodeReference $codeReference
     * @return Tide
     */
    public static function createFromFlow(Flow $flow, CodeReference $codeReference)
    {
        $uuid = Uuid::uuid1();
        $tide = new self($uuid);
        $tide->apply(new TideStarted($uuid, $flow, $codeReference));

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
            $this->codeRepository = $event->getFlow()->getRepository();
            $this->user = $event->getFlow()->getUser();
            $this->codeReference = $event->getCodeReference();
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
     * @return CodeRepository
     */
    public function getCodeRepository()
    {
        return $this->codeRepository;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }
}
