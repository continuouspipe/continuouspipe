<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Task\TaskList;
use Rhumsaa\Uuid\Uuid;

class Tide
{
    /**
     * This flag is set to true when we need to reconstruct a tide. It is locked
     * to prevent starting tasks.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * @var TideEvent[]
     */
    private $events = [];

    /**
     * @var TaskList
     */
    private $tasks;

    /**
     * @var TideContext
     */
    private $context;

    /**
     * @var TideEvent[]
     */
    private $newEvents = [];

    /**
     * @param TaskList $taskList
     */
    public function __construct(TaskList $taskList)
    {
        $this->tasks = $taskList;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->getContext()->getTideUuid();
    }

    /**
     * Create a new tide.
     *
     * @param TaskList    $tasks
     * @param TideContext $context
     *
     * @return Tide
     */
    public static function create(TaskList $tasks, TideContext $context)
    {
        $tide = new self($tasks);
        $event = new TideCreated($context, $tasks);
        $tide->apply($event);
        $tide->newEvents = [$event];

        return $tide;
    }

    /**
     * @param TaskList $tasks
     * @param $events
     *
     * @return Tide
     */
    public static function fromEvents(TaskList $tasks, $events)
    {
        $tide = new self($tasks);
        $tide->locked = true;
        foreach ($events as $event) {
            $tide->apply($event);
        }

        $tide->popNewEvents();
        $tide->locked = false;

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
        }

        $this->tasks->apply($event);

        if ($this->isRunning() && !$this->locked) {
            $this->handleTasks($event);
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

        // Get tasks' events
        foreach ($this->tasks->getTasks() as $tasks) {
            foreach ($tasks->popNewEvents() as $event) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * @return TideContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return TaskList
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param TideCreated $event
     */
    private function applyTideCreated(TideCreated $event)
    {
        $this->context = $event->getTideContext();
    }

    /**
     * @param TideEvent $event
     */
    private function handleTasks(TideEvent $event)
    {
        if ($this->tasks->hasRunning()) {
            return;
        } elseif ($this->tasks->hasFailed()) {
            $this->newEvents[] = new TideFailed($event->getTideUuid());
        } elseif (null !== ($nextTask = $this->tasks->next())) {
            $nextTask->start($this->context);
        } elseif ($this->tasks->allSuccessful()) {
            $this->newEvents[] = new TideSuccessful($event->getTideUuid());
        }
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->isStarted() && !$this->isFailed() && !$this->isSuccessful();
    }

    /**
     * @return bool
     */
    private function isStarted()
    {
        return 0 < $this->numberOfEventsOfType(TideStarted::class);
    }

    /**
     * @return bool
     */
    private function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(TideFailed::class);
    }

    /**
     * @return bool
     */
    private function isSuccessful()
    {
        return 0 < $this->numberOfEventsOfType(TideSuccessful::class);
    }

    /**
     * @param string $eventType
     *
     * @return int
     */
    private function numberOfEventsOfType($eventType)
    {
        $tideFinishedEvents = array_filter($this->events, function (TideEvent $event) use ($eventType) {
            return get_class($event) == $eventType || is_subclass_of($event, $eventType);
        });

        return count($tideFinishedEvents);
    }
}
