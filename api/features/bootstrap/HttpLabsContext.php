<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\HttpLabs\Client\Stack;
use ContinuousPipe\HttpLabs\PredictableClient;
use ContinuousPipe\HttpLabs\TraceableClient;

class HttpLabsContext implements Context
{
    /**
     * @var PredictableClient
     */
    private $predictableClient;

    /**
     * @var TraceableClient
     */
    private $traceableClient;

    public function __construct(PredictableClient $predictableClient, TraceableClient $traceableClient)
    {
        $this->predictableClient = $predictableClient;
        $this->traceableClient = $traceableClient;
    }

    /**
     * @Given the created HttpLabs stack will have the UUID :uuid and the URL address :url
     */
    public function theCreatedHttplabsStackWillHaveTheUuidAndTheUrlAddress($uuid, $url)
    {
        $this->predictableClient->setFutureStack(new Stack($uuid, $url));
    }

    /**
     * @Then an HttpLabs stack should have been created with the backend :backend
     */
    public function anHttplabsStackShouldHaveBeenCreatedWithTheBackend($backend)
    {
        foreach ($this->traceableClient->getCreatedStacks() as $stack) {
            if ($stack['backend_url'] == $backend) {
                return;
            }
        }

        throw new \RuntimeException('No stack created with this backend URL');
    }
}
