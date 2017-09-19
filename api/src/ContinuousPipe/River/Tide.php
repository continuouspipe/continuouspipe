<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\NotifiedPendingTideReason;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideGenerated;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Event\TideValidated;
use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Event\TideCancelled;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Task\TaskSkipped;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Tide
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'success';
    const STATUS_CANCELLED = 'cancelled';

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
     * @var string|null
     */
    private $failureReason;

    /**
     * @var string
     */
    private $status = Tide::STATUS_PENDING;

    /**
     * @var bool|null
     */
    private $isContinuousPipeFileExists;

    /**
     * @var string|null
     */
    private $pendingTideNotificationLogIdentifier;

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
            new TideCreated($context->getTideUuid(), $context->getFlowUuid(), $context, $generationRequest->getGenerationUuid(), $pipeline, $generationRequest->getContinuousPipeFileExists()),
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
        if ($event instanceof TideCreated || $event instanceof TideGenerated) {
            if ($event instanceof TideCreated) {
                $this->context = $event->getTideContext();
                $this->isContinuousPipeFileExists  = $event->hasContinuousPipeFile();
            }

            $this->generationUuid = $event->getGenerationUuid();
            $this->pipeline = $event->getFlatPipeline();
        } elseif (!$event instanceof TideFailed) {
            $this->tasks->apply($event);
        }

        if ($event instanceof TideCreated) {
            $this->status = self::STATUS_PENDING;
        } elseif ($event instanceof TideStarted) {
            $this->status = self::STATUS_RUNNING;
        } elseif ($event instanceof TideSuccessful) {
            $this->status = self::STATUS_SUCCESS;
        } elseif ($event instanceof TideFailed) {
            $this->status = self::STATUS_FAILURE;
            $this->failureReason = $event->getReason();
        } elseif ($event instanceof TideCancelled) {
            $this->status = self::STATUS_CANCELLED;
            $this->failureReason = 'Tide was cancelled';
        } elseif ($event instanceof NotifiedPendingTideReason) {
            $this->pendingTideNotificationLogIdentifier = $event->getLogIdentifier();
        }
    }

    public function start()
    {
        if ($this->status != self::STATUS_PENDING) {
            return;
        }

        if (0 === $this->tasks->count()) {
            if ($this->isContinuousPipeFileExists) {
                throw new TideConfigurationException('You need to configure tasks to be run for the tide.');
            }

            throw new TideConfigurationException('No `continuous-pipe.yml` file was found in the code repository.');
        } else {
            $this->events->raiseAndApply(new TideStarted(
                $this->getUuid()
            ));
        }
    }

    public function hasFailed(\Throwable $reason)
    {
        $this->events->raiseAndApply(new TideFailed(
            $this->getUuid(),
            $reason->getMessage()
        ));
    }

    /**
     * Cancel the tide.
     *
     * @param string $username The username of the user who triggered the command.
     */
    public function cancel(string $username)
    {
        $this->events->raiseAndApply(new TideCancelled(
            $this->getUuid(),
            $username
        ));
    }

    public function notifyPendingReason(LoggerFactory $loggerFactory, string $reason)
    {
        if (null !== $this->pendingTideNotificationLogIdentifier) {
            return;
        }

        $log = $loggerFactory->from($this->getLog())->child(new Text($reason))->getLog();

        $this->events->raiseAndApply(new NotifiedPendingTideReason(
            $this->getUuid(),
            $log->getId()
        ));
    }

    private function hop()
    {
        if (!$this->is(self::STATUS_RUNNING)) {
            return;
        }

        if (null !== ($failedTask = $this->tasks->getFailedTask())) {
            $this->events->raiseAndApply(new TideFailed(
                $this->getUuid(),
                sprintf('Task "%s" failed', $failedTask->getIdentifier())
            ));
        } elseif ($this->tasks->allSuccessful()) {
            $this->events->raiseAndApply(new TideSuccessful($this->getUuid()));
        } elseif (!$this->tasks->hasRunning() && !$this->is(self::STATUS_FAILURE)) {
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

    /**
     * Run the next task if possible.
     */
    private function nextTask()
    {
        if (null !== ($nextTask = $this->tasks->next())) {
            $this->taskRunner->run($this, $nextTask);
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

    public function getUuid() : UuidInterface
    {
        return $this->getContext()->getTideUuid();
    }

    public function getContext() : TideContext
    {
        return $this->context;
    }

    public function getTasks() : TaskList
    {
        return $this->tasks;
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

    /**
     * @return null|string
     */
    public function getFailureReason()
    {
        return $this->failureReason;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function is(string $status) : bool
    {
        return $this->status == $status;
    }
}
