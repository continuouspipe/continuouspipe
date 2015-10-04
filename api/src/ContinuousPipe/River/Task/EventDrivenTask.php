<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;

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
        $events = array_filter($this->events->getEvents(), function (TideEvent $event) use ($className) {
            return get_class($event) == $className || is_subclass_of($event, $className);
        });

        return array_values($events);
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
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getExposedContext()
    {
        return [];
    }
}
