<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Tests\Intercom\TraceableIntercomClient;

class IntercomContext implements Context
{
    /**
     * @var TraceableIntercomClient
     */
    private $traceableIntercomClient;

    /**
     * @param TraceableIntercomClient $traceableIntercomClient
     */
    public function __construct(TraceableIntercomClient $traceableIntercomClient)
    {
        $this->traceableIntercomClient = $traceableIntercomClient;
    }

    /**
     * @Then an intercom lead should be created for the email :email
     */
    public function anIntercomLeadShouldBeCreatedForTheEmail($email)
    {
        $matchingLeads = array_filter($this->traceableIntercomClient->getCreatedLeads(), function(array $lead) use ($email) {
            return $lead['email'] == $email;
        });

        if (count($matchingLeads) == 0) {
            throw new \RuntimeException('No matching created lead found');
        }
    }
}
