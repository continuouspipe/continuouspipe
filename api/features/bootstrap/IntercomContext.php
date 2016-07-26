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

    /**
     * @Then an intercom message should have been sent to the lead :lead
     */
    public function anIntercomMessageShouldHaveBeenSentToTheLead($lead)
    {
        $matchingMessages = array_filter($this->traceableIntercomClient->getSentMessages(), function(array $message) use ($lead) {
            return $message['to']['id'] == $lead;
        });

        if (count($matchingMessages) == 0) {
            throw new \RuntimeException('No matching message found');
        }
    }

    /**
     * @Then an intercom user :username should be created or updated
     */
    public function anIntercomUserShouldBeCreatedOrUpdated($username)
    {
        $matchingUsers = array_filter($this->traceableIntercomClient->getCreatedOrUpdatedUsers(), function(array $user) use ($username) {
            return $user['user_id'] == $username;
        });

        if (count($matchingUsers) == 0) {
            throw new \RuntimeException('No matching user found');
        }
    }

    /**
     * @Then an intercom event :name should be created
     */
    public function anIntercomEventShouldBeCreated($name)
    {
        if (null === $this->findCreatedEventByName($name)) {
            throw new \RuntimeException('Created event not found');
        }
    }

    /**
     * @Then an intercom event :name should not be created
     */
    public function anIntercomEventShouldNotBeCreated($name)
    {
        if (null !== $this->findCreatedEventByName($name)) {
            throw new \RuntimeException('Created event found');
        }
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    private function findCreatedEventByName($name)
    {
        $matchingEvents = array_filter($this->traceableIntercomClient->getCreatedEvents(), function(array $event) use ($name) {
            return $event['event_name'] == $name;
        });

        return array_pop($matchingEvents);
    }
}
