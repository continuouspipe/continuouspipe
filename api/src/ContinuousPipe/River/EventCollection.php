<?php

namespace ContinuousPipe\River;

class EventCollection implements \IteratorAggregate
{
    private $events = [];

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
     * @param $event
     */
    public function add($event)
    {
        $this->events[] = $event;
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
}
