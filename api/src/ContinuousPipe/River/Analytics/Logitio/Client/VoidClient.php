<?php

namespace ContinuousPipe\River\Analytics\Logitio\Client;

class VoidClient implements LogitioClient
{
    /**
     * {@inheritdoc}
     */
    public function addEvent($logType, array $event)
    {
    }
}
