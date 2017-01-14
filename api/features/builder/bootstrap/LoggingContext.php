<?php

use Behat\Behat\Context\Context;
use LogStream\TraceableClient;

class LoggingContext implements Context
{
    /**
     * @var TraceableClient
     */
    private $traceableClient;

    public function __construct(TraceableClient $traceableClient)
    {
        $this->traceableClient = $traceableClient;
    }

    /**
     * @Then a log containing :text should be created
     */
    public function aLogContainingShouldBeCreated($text)
    {
        foreach ($this->traceableClient->getCreated() as $created) {
            $serialized = $created->getNode()->jsonSerialize();

            if ($serialized['type'] == 'text' && false !== strpos($serialized['contents'], $text)) {
                return;
            }
        }

        throw new \RuntimeException('No matching log found');
    }
}
