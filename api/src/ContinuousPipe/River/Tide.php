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
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Task\TaskSkipped;
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
     * @var UuidInterface|null
     */
    private $generationUuid;

    /**
     * @var FlatPipeline
     */
    private $pipeline;

    /**
     * @param TaskRunner      $taskRunner
     * @param TaskList        $taskList
     * @param EventCollection $events
     */
    public function __construct(TaskRunner $taskRunner, TaskList $taskList, EventCollection $events)
    {
        $this->taskRunner = $taskRunner;
        $this->tasks = $taskList;
        $this->events = $events;
        $this->events->onRaised(function (TideEvent $event) {
            $this->apply($event);
            $this->hop();
        });
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
     * @param EventCollection       $eventCollection
     *
     * @return Tide
     */
    public static function create(
        TaskRunner $taskRunner,
        TaskList $tasks,
        TideContext $context,
        TideGenerationRequest $generationRequest,
        FlatPipeline $pipeline,
        EventCollection $eventCollection
    ) {
        return self::createFromEvents($taskRunner, $tasks, $eventCollection, [
            new TideCreated($context),
            new TideGenerated($context->getTideUuid(), $context->getFlowUuid(), $generationRequest->getGenerationUuid(), $pipeline),
            new TideValidated($context->getTideUuid()),
        ]);
    }

    /**
     * Create the tide from a set of events.
     *
     * @param TaskRunner      $taskRunner
     * @param TaskList        $tasks
     * @param EventCollection $eventCollection
     * @param TideEvent[]     $events
     *
     * @return Tide
     */
    public static function createFromEvents(
        TaskRunner $taskRunner,
        TaskList $tasks,
        EventCollection $eventCollection,
        array $events
    ) {
        $tide = new self($taskRunner, $tasks, $eventCollection);

        foreach ($events as $event) {
            $eventCollection->raiseAndApply($event);
        }

        return $tide;
    }

    /**
     * @param TaskRunner      $taskRunner
     * @param TaskList        $tasks
     * @param EventCollection $events
     *
     * @return Tide
     */
    public static function fromEvents(TaskRunner $taskRunner, TaskList $tasks, EventCollection $events)
    {
        $tide = new self($taskRunner, $tasks, $events);
        foreach ($events as $event) {
            $tide->apply($event);
        }

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
            $this->context = $event->getTideContext();
        } elseif ($event instanceof TideGenerated) {
            $this->generationUuid = $event->getGenerationUuid();
            $this->pipeline = $event->getFlatPipeline();
        } elseif (!$event instanceof TideFailed) {
            $this->tasks->apply($event);
        }
    }

    /**
     * @return TideEvent[]
     */
    public function popNewEvents()
    {
        $events = $this->events->getRaised();

        $this->events->clearRaised();

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
     * @return FlatPipeline|null
     */
    public function getPipeline()
    {
        if (null === $this->pipeline) {
            return null;
        }

        return $this->pipeline;
    }

    public function start()
    {
        $this->events->raiseAndApply(new TideStarted(
            $this->getUuid()
        ));
    }

    private function hop()
    {
        if (!$this->isRunning()) {
            return;
        }

        if (null !== ($failedTask = $this->tasks->getFailedTask())) {
            $this->events->raiseAndApply(new TideFailed(
                $this->getUuid(),
                sprintf('Task "%s" failed', $failedTask->getIdentifier())
            ));
        } elseif ($this->tasks->allSuccessful()) {
            $this->events->raiseAndApply(new TideSuccessful($this->getUuid()));
        } elseif (!$this->tasks->hasRunning() && !$this->isFailed()) {
            try {
                $this->nextTask();
            } catch (TaskRunnerException $e) {
                $this->events->raiseAndApply(new TideFailed($this->getUuid(), $e->getMessage()));
            }
        }
    }

    public function skipTask(Task $task)
    {
        $this->events->raiseAndApply(new TaskSkipped(
            $this->getUuid(),
            $task->getIdentifier(),
            $task->getLogIdentifier()
        ));
    }
}
