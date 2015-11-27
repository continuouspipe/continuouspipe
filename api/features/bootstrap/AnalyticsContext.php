<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Tests\Analytics\Keen\TraceableKeenClient;

class AnalyticsContext implements Context
{
    /**
     * @var TraceableKeenClient
     */
    private $traceableKeenClient;

    /**
     * @param TraceableKeenClient $traceableKeenClient
     */
    public function __construct(TraceableKeenClient $traceableKeenClient)
    {
        $this->traceableKeenClient = $traceableKeenClient;
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
}
