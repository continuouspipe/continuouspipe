<?php

namespace ContinuousPipe\River\Tests\Analytics\Logitio;

use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;

class TraceableLogitioClient implements LogitioClient
{
    /**
     * @var LogitioClient
     */
    private $client;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @param LogitioClient $client
     */
    public function __construct(LogitioClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($logType, array $event)
    {
        if (!array_key_exists($logType, $this->events)) {
            $this->events[$logType] = [];
        }

        $this->events[$logType][] = $event;

        return $this->client->addEvent($logType, $event);
    }

    /**
     * @param string $logType
     *
     * @return array
     */
    public function getEvents($logType)
    {
        return $this->events[$logType];
    }

    /**
     * @param string $logType
     *
     * @return bool
     */
    public function hasLogType($logType)
    {
        return array_key_exists($logType, $this->events);
    }
}
