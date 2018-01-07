<?php

namespace ContinuousPipe\River\Tests\Analytics\Keen;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;

class TraceableKeenClient implements KeenClient
{
    /**
     * @var KeenClient
     */
    private $client;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @param KeenClient $client
     */
    public function __construct(KeenClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($collection, array $event)
    {
        if (!array_key_exists($collection, $this->events)) {
            $this->events[$collection] = [];
        }

        $this->events[$collection][] = $event;

        return $this->client->addEvent($collection, $event);
    }

    /**
     * @param string $collection
     *
     * @return array
     */
    public function getEvents($collection)
    {
        return $this->events[$collection];
    }

    /**
     * @param string $collection
     *
     * @return bool
     */
    public function hasCollection($collection)
    {
        return array_key_exists($collection, $this->events);
    }
}
