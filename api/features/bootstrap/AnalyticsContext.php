<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Tests\Analytics\Keen\TraceableKeenClient;
use ContinuousPipe\River\Tests\Analytics\Logitio\TraceableLogitioClient;

class AnalyticsContext implements Context
{
    /**
     * @var TraceableKeenClient
     */
    private $traceableKeenClient;
    private $traceableLogitioClient;

    /**
     * @param TraceableKeenClient $traceableKeenClient
     */
    public function __construct(TraceableKeenClient $traceableKeenClient, TraceableLogitioClient $traceableLogitioClient)
    {
        $this->traceableKeenClient = $traceableKeenClient;
        $this->traceableLogitioClient = $traceableLogitioClient;
    }

    /**
     * @Then an event should be sent to keen in the collection :collection
     */
    public function anEventShouldBeSentToKeenInTheCollection($collection)
    {
        if (!$this->traceableKeenClient->hasCollection($collection)) {
            throw new \RuntimeException(sprintf(
                'Collection "%s" is not found',
                $collection
            ));
        }

        $events = $this->traceableKeenClient->getEvents($collection);
        if (0 === count($events)) {
            throw new \RuntimeException('Expected to find events in the collection, found 0');
        }
    }

    /**
     * @Then an event should be sent to logitio in the collection :collection
     */
    public function anEventShouldBeSentToLogitioInTheCollection($collection)
    {
        if (!$this->traceableLogitioClient->hasLogType($collection)) {
            throw new \RuntimeException(sprintf(
                'Collection "%s" is not found',
                $collection
            ));
        }

        $events = $this->traceableLogitioClient->getEvents($collection);
        if (0 === count($events)) {
            throw new \RuntimeException('Expected to find events in the collection, found 0');
        }
    }

    /**
     * @Then a :type event should be sent to logitio with the UUID :uuid and status code :status
     */
    public function anEventShouldBeSentToLogitioWithTheUuidAndStatusCode($type, $uuid, $status)
    {
        if (!$this->traceableLogitioClient->hasLogType($type)) {
            throw new \RuntimeException(sprintf(
                'Log type "%s" not found',
                $type
            ));
        }

        $events = $this->traceableLogitioClient->getEvents($type);
        if (0 === count($events)) {
            throw new \RuntimeException("Expected to find events of type $type, found 0");
        }

        if (0 == count(array_filter($events, function ($event) use($uuid, $status) {
            return isset($event['tide_uuid']) &&$event['tide_uuid'] == $uuid && isset($event['status']['code']) && $event['status']['code'] == $status;
        }))) {
           throw new \RuntimeException("Expected to find event with uuid $uuid and status code $status");
        }

    }

    /**
     * @Then a :type event should be sent to logitio with the status code :status
     */
    public function aEventShouldBeSentToLogitioWithTheStatusCode($type, $status)
    {
        if (!$this->traceableLogitioClient->hasLogType($type)) {
            throw new \RuntimeException(sprintf(
                'Log type "%s" not found',
                $type
            ));
        }

        $events = $this->traceableLogitioClient->getEvents($type);
        if (0 === count($events)) {
            throw new \RuntimeException("Expected to find events of type $type, found 0");
        }

        if (0 == count(array_filter($events, function ($event) use($status) {
                return isset($event['status']['code']) && $event['status']['code'] == $status;
            }))) {
            throw new \RuntimeException("Expected to find event with status code $status");
        }
    }
}
