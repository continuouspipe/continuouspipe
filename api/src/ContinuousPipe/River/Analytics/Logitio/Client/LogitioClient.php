<?php

namespace ContinuousPipe\River\Analytics\Logitio\Client;

interface LogitioClient
{
    /**
     * Add a new event.
     *
     * @param string $logType
     * @param array  $event
     */
    public function addEvent($logType, array $event);
}
