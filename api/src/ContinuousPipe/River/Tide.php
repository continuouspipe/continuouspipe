<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\Build\BuildFailed;
use ContinuousPipe\River\Event\Build\BuildSuccessful;
use ContinuousPipe\River\Event\ImageBuildsFailed;
use ContinuousPipe\River\Event\ImageBuildsStarted;
use ContinuousPipe\River\Event\ImagesBuilt;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\User\User;
use LogStream\Log;
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
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var Log
     */
    private $parentLog;

    /**
     * @var Flow
     */
    private $flow;

    /**
     * Create a new tide.
     *
     * @param Uuid          $uuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     * @param Log           $parentLog
     *
     * @return Tide
     */
    public static function create(Uuid $uuid, Flow $flow, CodeReference $codeReference, Log $parentLog)
    {
        $startEvent = new TideCreated($uuid, $flow, $codeReference, $parentLog);

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
        if ($event instanceof TideCreated) {
            $this->applyTideCreated($event);
        } elseif ($event instanceof BuildSuccessful) {
            $this->applyBuildSuccessful($event);
        } elseif ($event instanceof BuildFailed) {
            $this->applyBuildFailed($event);
        } elseif ($event instanceof ImageBuildsFailed) {
            $this->applyImageBuildsFailed($event);
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
     * @param TideCreated $event
     */
    private function applyTideCreated(TideCreated $event)
    {
        $this->uuid = $event->getTideUuid();
        $this->flow = $event->getFlow();
        $this->codeReference = $event->getCodeReference();
        $this->parentLog = $event->getParentLog();
    }

    /**
     * @param BuildSuccessful $event
     */
    private function applyBuildSuccessful(BuildSuccessful $event)
    {
        if ($this->allImageBuildsSuccessful()) {
            $eventImageBuildsStarted = $this->getImageBuildsStartedEvent();

            $this->newEvents[] = new ImagesBuilt($this->uuid, $eventImageBuildsStarted->getLog());
        }
    }

    /**
     * @param BuildFailed $event
     */
    private function applyBuildFailed(BuildFailed $event)
    {
        $eventImageBuildsStarted = $this->getImageBuildsStartedEvent();

        $this->newEvents[] = new ImageBuildsFailed($this->uuid, $eventImageBuildsStarted->getLog());
    }

    /**
     * @param ImageBuildsFailed $event
     */
    private function applyImageBuildsFailed(ImageBuildsFailed $event)
    {
        $this->newEvents[] = new TideFailed($this->uuid);
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Flow
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @return CodeRepository
     */
    public function getCodeRepository()
    {
        return $this->getCodeReference()->getRepository();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->getFlow()->getUser();
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * @return Log
     */
    public function getParentLog()
    {
        return $this->parentLog;
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

    /**
     * @return ImageBuildsStarted
     */
    private function getImageBuildsStartedEvent()
    {
        return $this->getEventsOfType(ImageBuildsStarted::class)[0];
    }
}
