<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideGenerated;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Event\TideValidated;
use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskFailed;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
     * @var UuidInterface|null
     */
    private $generationUuid;

    /**
     * @var FlatPipeline
     */
    private $pipeline;

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
     * @param TaskRunner            $taskRunner
     * @param TaskList              $tasks
     * @param TideContext           $context
     * @param TideGenerationRequest $generationRequest
     * @param FlatPipeline          $pipeline
     *
     * @return Tide
     */
    public static function create(
        TaskRunner $taskRunner,
        TaskList $tasks,
        TideContext $context,
        TideGenerationRequest $generationRequest,
        FlatPipeline $pipeline
    ) {
        $tide = new self($taskRunner, $tasks);
        $events = [
            new TideCreated($context),
            new TideGenerated($context->getTideUuid(), $context->getFlowUuid(), $generationRequest->getGenerationUuid(), $pipeline),
            new TideValidated($context->getTideUuid()),
        ];

        foreach ($events as $event) {
            $tide->apply($event);
        }

        $tide->newEvents = $events;

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
        } elseif ($event instanceof TideGenerated) {
            $this->generationUuid = $event->getGenerationUuid();
            $this->pipeline = $event->getFlatPipeline();
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
     * @param string $identifier
     *
     * @throws \InvalidArgumentException
     *
     * @return Task
     */
    public function getTask(string $identifier) : Task
    {
        foreach ($this->tasks->getTasks() as $task) {
            if ($task->getIdentifier() == $identifier) {
                return $task;
            }
        }

        throw new \InvalidArgumentException(sprintf('The task identified "%s" is not found', $identifier));
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
        if (null !== ($failedTask = $this->tasks->getFailedTask())) {
            $this->newEvents[] = new TideFailed(
                $event->getTideUuid(),
                sprintf('Task "%s" failed', $failedTask->getIdentifier())
            );
        } elseif ($this->tasks->allSuccessful()) {
            $this->newEvents[] = new TideSuccessful($event->getTideUuid());
        } elseif (!$this->tasks->hasRunning() && !$event instanceof TaskFailed) {
            try {
                $this->nextTask();
            } catch (TaskRunnerException $e) {
                $task = $e->getTask();

                $this->newEvents[] = new TaskFailed($event->getTideUuid(), $task->getIdentifier(), $task->getLogIdentifier(), $e->getMessage());
                $this->newEvents[] = new TideFailed($event->getTideUuid(), $e->getMessage());
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

            if ($nextTask->getStatus() == Task::STATUS_SKIPPED) {
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
    public function isStarted()
    {
        return 0 < $this->events->numberOfEventsOfType(TideStarted::class);
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return 0 < $this->events->numberOfEventsOfType(TideFailed::class);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
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

    public function getFlowUuid() : UuidInterface
    {
        return $this->context->getFlowUuid();
    }

    public function getCodeReference() : CodeReference
    {
        return $this->context->getCodeReference();
    }

    public function getLog() : Log
    {
        return $this->context->getLog();
    }

    public function getTeam() : Team
    {
        return $this->context->getTeam();
    }

    public function getUser() : User
    {
        return $this->context->getUser();
    }

    public function getConfiguration() : array
    {
        return $this->context->getConfiguration() ?: [];
    }

    /**
     * @return null|UuidInterface
     */
    public function getGenerationUuid()
    {
        return $this->generationUuid;
    }

    /**
     * @return UuidInterface|null
     */
    public function getPipelineUuid()
    {
        if (null === $this->pipeline) {
            return null;
        }

        return $this->pipeline->getUuid();
    }
}
