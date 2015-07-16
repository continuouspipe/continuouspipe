<?php

namespace ContinuousPipe\River;

use Rhumsaa\Uuid\Uuid;

class Tide
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var array
     */
    private $events;

    /**
     * @param Uuid $uuid
     */
    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Create a new tide.
     *
     * @return Tide
     */
    public static function create()
    {
        return new self(Uuid::uuid1());
    }

    public function apply($event)
    {
        $this->events[] = $event;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
