<?php

namespace ContinuousPipe\River\EventBased;

trait RaiseEventCapability
{
    private $raisedEvents = [];

    protected function raise($event)
    {
        $this->raisedEvents[] = $event;
    }

    public function raisedEvents() : array
    {
        return $this->raisedEvents;
    }

    public function eraseEvents()
    {
        $this->raisedEvents = [];
    }
}
