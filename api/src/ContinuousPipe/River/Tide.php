<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\Build\BuildSuccessful;
use ContinuousPipe\River\Event\ImageBuildsStarted;
use ContinuousPipe\River\Event\ImagesBuilt;
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
    private $events = [];

    /**
     * @var TideEvent[]
     */
    private $newEvents = [];

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
     * Create a new tide.
     *
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return Tide
     */
    public static function createFromFlow(Flow $flow, CodeReference $codeReference)
    {
        $startEvent = new TideStarted(Uuid::uuid1(), $flow, $codeReference);

        $tide = new self();
        $tide->apply($startEvent);
        $tide->newEvents = [$startEvent];

        return $tide;
    }

    /**
     * Create a tide based on this events.
     *
     * @param TideEvent[] $events
     *
     * @return Tide
     */
    public static function fromEvents(array $events)
    {
        $tide = new self();
        foreach ($events as $event) {
            $tide->apply($event);
        }

        $tide->popNewEvents();

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
            $this->applyTideStarted($event);
        } elseif ($event instanceof BuildSuccessful) {
            $this->applyBuildSuccessful($event);
        }

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
     * @param TideStarted $event
     */
    private function applyTideStarted(TideStarted $event)
    {
        $this->uuid = $event->getTideUuid();
        $this->user = $event->getFlow()->getUser();
        $this->codeRepository = $event->getFlow()->getRepository();
        $this->codeReference = $event->getCodeReference();
    }

    /**
     * @param BuildSuccessful $event
     */
    private function applyBuildSuccessful(BuildSuccessful $event)
    {
        if ($this->allImageBuildsSuccessful()) {
            $this->newEvents[] = new ImagesBuilt($this->uuid);
        }
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

    /**
     * @param string $className
     *
     * @return TideEvent[]
     */
    private function getEventsOfType($className)
    {
        $events = array_filter($this->events, function (TideEvent $event) use ($className) {
            return get_class($event) == $className;
        });

        return array_values($events);
    }

    /**
     * Check if all the started builds are successful.
     *
     * @return bool
     *
     * @throws BuildNotFound
     */
    private function allImageBuildsSuccessful()
    {
        $buildsStartedEvents = $this->getEventsOfType(ImageBuildsStarted::class);
        if (count($buildsStartedEvents) == 0) {
            throw new BuildNotFound('No started build found');
        }

        /** @var ImageBuildsStarted $buildsStartedEvent */
        $buildsStartedEvent = $buildsStartedEvents[0];
        $numberOfStartedBuilds = count($buildsStartedEvent->getBuildRequests());
        $numberOfSuccessfulBuilds = count($this->getEventsOfType(BuildSuccessful::class));

        return $numberOfSuccessfulBuilds == $numberOfStartedBuilds;
    }
}
