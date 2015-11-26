<?php

namespace ContinuousPipe\River\Analytics\Keen\Client;

interface KeenClient
{
    /**
     * Add a new event.
     *
     * @param string $collection
     * @param array  $event
     */
    public function addEvent($collection, array $event);
}
