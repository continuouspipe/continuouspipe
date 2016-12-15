<?php

namespace ContinuousPipe\River\EventBased;

trait ApplyAndRaiseEventCapability
{
    private $raisedEvents = [];

    public static function fromEvents(array $events)
    {
        $self = new static();

        foreach ($events as $event) {
            $self->apply($event);
        }

        return $self;
    }

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

    public function apply($event)
    {
        $className = get_class($event);
        $classNameTail = substr($className, strrpos($className, '\\') + 1);

        $method = sprintf('apply%s', ucfirst($classNameTail));
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(
                "There is no event named '$method' that can be applied to '".get_class($this)."'. ".
                'If you just want to emit an event without applying changes use the raise() method.'
            );
        }

        $this->$method($event);
    }
}
