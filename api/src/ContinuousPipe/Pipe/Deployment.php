<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Event\DeploymentEvent;

class Deployment
{
    /**
     * @var DeploymentEvent[]
     */
    private $events = [];

    /**
     * @var DeploymentEvent[]
     */
    private $newEvents = [];

    /**
     * @param
     * @param $events
     *
     * @return Deployment
     */
    public static function fromEvents($events)
    {
        $tide = new self();
        foreach ($events as $event) {
            $tide->apply($event);
        }

        $tide->popNewEvents();

        return $tide;
    }

    /**
     * Apply a given event.
     *
     * @param DeploymentEvent $event
     */
    public function apply(DeploymentEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return DeploymentEvent[]
     */
    public function popNewEvents()
    {
        $events = $this->newEvents;

        $this->newEvents = [];

        return $events;
    }
}
