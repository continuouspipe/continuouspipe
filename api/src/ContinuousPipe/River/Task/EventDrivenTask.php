<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;

abstract class EventDrivenTask implements Task
{
    private $events = [];
    protected $newEvents = [];

    /**
     * @param TideEvent $event
     */
    public function apply(TideEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * @param string $className
     *
     * @return TideEvent[]
     */
    protected function getEventsOfType($className)
    {
        $events = array_filter($this->events, function (TideEvent $event) use ($className) {
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
}
