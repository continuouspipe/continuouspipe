<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Task\TaskFailed;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use Rhumsaa\Uuid\Uuid;

class Tide
{
    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * This flag is set to true when we need to reconstruct a tide. It is locked
     * to prevent starting tasks.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * @var EventCollection
     */
    private $events;

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
     * @param TaskRunner $taskRunner
     * @param TaskList   $taskList
     */
    public function __construct(TaskRunner $taskRunner, TaskList $taskList)
    {
        $this->taskRunner = $taskRunner;
        $this->tasks = $taskList;
        $this->events = new EventCollection();
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
     * @param TaskRunner  $taskRunner
     * @param TaskList    $tasks
     * @param TideContext $context
     *
     * @return Tide
     */
    public static function create(TaskRunner $taskRunner, TaskList $tasks, TideContext $context)
    {
        $tide = new self($taskRunner, $tasks);
        $event = new TideCreated($context);
        $tide->apply($event);
        $tide->newEvents = [$event];

        return $tide;
    }

    /**
     * @param TaskRunner  $taskRunner
     * @param TaskList    $tasks
     * @param TideEvent[] $events
     *
     * @return Tide
     */
    public static function fromEvents(TaskRunner $taskRunner, TaskList $tasks, $events)
    {
        $tide = new self($taskRunner, $tasks);
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
        } elseif (!$event instanceof TideFailed) {
            $this->tasks->apply($event);

            if (($event instanceof TideStarted || $this->isRunning()) && !$this->locked) {
                $this->handleTasks($event);
            }
        }

        $this->events->add($event);
    }

    /**
     * @return TideEvent[]
     */
    public function popNewEvents()
    {
        $events = $this->newEvents;
        $this->newEvents = [];

        // Get tasks' events
        foreach ($this->tasks->getTasks() as $task) {
            foreach ($task->popNewEvents() as $event) {
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
        if ($this->tasks->hasFailed()) {
            $this->newEvents[] = new TideFailed($event->getTideUuid());
        } elseif ($this->tasks->allSuccessful()) {
            $this->newEvents[] = new TideSuccessful($event->getTideUuid());
        } elseif (!$this->tasks->hasRunning() && !$event instanceof TaskFailed) {
            try {
                $this->nextTask();
            } catch (TaskRunnerException $e) {
                $this->newEvents[] = new TaskFailed($event->getTideUuid(), $e->getTask()->getContext(), $e->getMessage());
                $this->newEvents[] = new TideFailed($event->getTideUuid());
            }
        }
    }

    /**
     * Run the next task if possible.
     */
    private function nextTask()
    {
        if (null !== ($nextTask = $this->tasks->next())) {
            $this->taskRunner->run($this, $nextTask);

            if ($nextTask->isSkipped()) {
                $this->nextTask();
            }
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
        return 0 < $this->events->numberOfEventsOfType(TideStarted::class);
    }

    /**
     * @return bool
     */
    private function isFailed()
    {
        return 0 < $this->events->numberOfEventsOfType(TideFailed::class);
    }

    /**
     * @return bool
     */
    private function isSuccessful()
    {
        return 0 < $this->events->numberOfEventsOfType(TideSuccessful::class);
    }

    /**
     * @param TideEvent $event
     */
    public function pushNewEvent(TideEvent $event)
    {
        $this->newEvents[] = $event;
    }
}
