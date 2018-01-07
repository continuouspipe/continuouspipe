<?php

namespace GitHub\WebHook;

class EventClassMapping
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping = [])
    {
        $this->mapping = $mapping;
    }

    /**
     * @param string $event
     * @param string $className
     */
    public function add($event, $className)
    {
        $this->mapping[$event] = $className;
    }

    /**
     * @param string $event
     *
     * @return string
     *
     * @throws EventClassNotFound
     */
    public function getEventClass($event)
    {
        if (!array_key_exists($event, $this->mapping)) {
            throw new EventClassNotFound(sprintf(
                'Class for event "%s" not found',
                $event
            ));
        }

        return $this->mapping[$event];
    }
}
