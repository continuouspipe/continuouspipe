<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventCollection;

abstract class EventDrivenTask implements Task
{
    /**
     * @var EventCollection
     */
    private $events;

    /**
     * @var array
     */
    protected $newEvents = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->events = new EventCollection();
    }

    /**
     * @param TideEvent $event
     */
    public function apply(TideEvent $event)
    {
        $this->events->add($event);
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
     * @return TideEvent[]
     */
    public function popNewEvents()
    {
        $events = $this->newEvents;
        $this->newEvents = [];

        return $events;
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
    public function clear()
    {
        $this->events->clear();
        $this->newEvents = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return $this->events;
    }
}
