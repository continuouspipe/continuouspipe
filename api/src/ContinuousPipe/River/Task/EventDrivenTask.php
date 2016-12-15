<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use LogStream\Node\Text;

abstract class EventDrivenTask implements Task
{
    /**
     * @var TaskContext
     */
    private $context;

    /**
     * @var EventCollection
     */
    protected $events;

    /**
     * @var array
     */
    protected $newEvents = [];

    /**
     * @param TaskContext $context
     */
    public function __construct(TaskContext $context)
    {
        $this->context = $context;
        $this->events = new EventCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        $this->events->add($event);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(TideEvent $event)
    {
        if ($event instanceof TaskEvent) {
            return $this->context->getTaskId() == $event->getTaskId();
        }

        return true;
    }

    /**
     * @param string $className
     *
     * @return TideEvent[]
     */
    protected function getEventsOfType($className)
    {
        $events = $this->events->getEvents();
        $matchingEvents = array_filter($events, function (TideEvent $event) use ($className) {
            return get_class($event) == $className || is_subclass_of($event, $className);
        });

        return array_values($matchingEvents);
    }

    /**
     * @param string $eventType
     *
     * @return int
     */
    protected function numberOfEventsOfType($eventType)
    {
        return count($this->getEventsOfType($eventType));
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return !$this->isFailed() && !$this->isSuccessful() && !$this->isPending();
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return 0 === $this->numberOfEventsOfType(TaskQueued::class);
    }

    /**
     * @return bool
     */
    public function isSkipped()
    {
        return $this->numberOfEventsOfType(TaskSkipped::class) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function popNewEvents()
    {
        $events = $this->newEvents;
        $this->newEvents = [];

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        if (null === $this->context->getTaskLog()) {
            /** @var TaskLogCreated[] $logCreatedEvents */
            $logCreatedEvents = $this->getEventsOfType(TaskLogCreated::class);

            if (count($logCreatedEvents) > 0) {
                $this->context->setTaskLog($logCreatedEvents[0]->getLog());
            }
        }

        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        if ($taskLog = $this->getContext()->getTaskLog()) {
            $node = $taskLog->getNode();

            if ($node instanceof Text) {
                return $node->getText();
            }
        }

        return $this->getIdentifier();
    }

    public function getIdentifier(): string
    {
        return $this->context->getTaskId();
    }

    public function getLogIdentifier(): string
    {
        if (null === ($log = $this->context->getTaskLog())) {
            $log = $this->context->getLog();
        }

        return $log->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getExposedContext()
    {
        return new ArrayObject([]);
    }

    /**
     * Is this task successful ?
     *
     * @return bool
     */
    abstract public function isSuccessful();

    /**
     * Is this task failed ?
     *
     * @return bool
     */
    abstract public function isFailed();

    /**
     * @return string
     */
    public function getStatus(): string
    {
        if ($this->isSkipped()) {
            return Task::STATUS_SKIPPED;
        } elseif ($this->isPending()) {
            return Task::STATUS_PENDING;
        } elseif ($this->isSuccessful()) {
            return Task::STATUS_SUCCESSFUL;
        } elseif ($this->isFailed()) {
            return Task::STATUS_FAILED;
        } elseif ($this->isRunning()) {
            return Task::STATUS_RUNNING;
        }

        throw new \RuntimeException('Unable to determinate curent task status');
    }
}
