<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideEvent;

class EventCollection implements \IteratorAggregate
{
    private $events = [];
    private $onRaisedHooks = [];
    private $raised = [];

    public function __construct(array $events = [])
    {
        $this->events = $events;
    }

    public function raiseAndApply(TideEvent $event)
    {
        $this->events[] = $event;
        $this->raised[] = $event;

        foreach ($this->onRaisedHooks as $hook) {
            $hook($event);
        }
    }

    /**
     * @param $event
     */
    public function removeIfExists($event)
    {
        foreach ($this->events as $index => $foundEvent) {
            if ($event === $foundEvent) {
                array_splice($this->events, $index, 1);

                break;
            }
        }
    }

    /**
     * @param string $eventType
     *
     * @return int
     */
    public function numberOfEventsOfType($eventType)
    {
        $tideFinishedEvents = array_filter($this->events, function ($event) use ($eventType) {
            return get_class($event) == $eventType || is_subclass_of($event, $eventType);
        });

        return count($tideFinishedEvents);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Clear the collection.
     */
    public function clear()
    {
        $this->events = [];
    }

    public function onRaised(callable $callable)
    {
        $this->onRaisedHooks[] = $callable;
    }

    public function getRaised()
    {
        return $this->raised;
    }

    public function clearRaised()
    {
        $this->raised = [];
    }
}
