<?php

namespace Authenticator;

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;
use ContinuousPipe\Authenticator\Intercom\HookableIntercomClient;
use ContinuousPipe\Authenticator\Tests\Intercom\TraceableIntercomClient;

class IntercomContext implements Context
{
    /**
     * @var TraceableIntercomClient
     */
    private $traceableIntercomClient;
    /**
     * @var HookableIntercomClient
     */
    private $hookableIntercomClient;

    public function __construct(TraceableIntercomClient $traceableIntercomClient, HookableIntercomClient $hookableIntercomClient)
    {
        $this->traceableIntercomClient = $traceableIntercomClient;
        $this->hookableIntercomClient = $hookableIntercomClient;
    }

    /**
     * @Given the intercom API will throw an exception
     */
    public function theIntercomApiWillThrowAnException()
    {
        $this->hookableIntercomClient->addHook(function() {
            throw new IntercomException('This is an expected exception');
        });
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
     * @Then an intercom message should have been sent to the email :email
     */
    public function anIntercomMessageShouldHaveBeenSentToTheEmail($email)
    {
        $matchingMessages = array_filter($this->traceableIntercomClient->getSentMessages(), function(array $message) use ($email) {
            return $message['to']['email'] == $email;
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
        if (null === $this->findCreatedOrUpdatedUser($username)) {
            throw new \RuntimeException('No matching user found');
        }
    }

    /**
     * @Then an intercom user :username should be updated with its companies
     */
    public function anIntercomUserShouldBeUpdatedWithItsCompanies($username)
    {
        $this->anIntercomUserShouldBeCreatedOrUpdated($username);

        $user = $this->findCreatedOrUpdatedUser($username);
        if (!array_key_exists('companies', $user)) {
            throw new \RuntimeException('No companies found in user');
        }

        if (!is_array($user['companies'])) {
            throw new \RuntimeException('Companies data must be an array');
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
     * @Then an intercom lead should be merged into the user :email
     */
    public function anIntercomLeadShouldBeMergedIntoTheUser($email)
    {
        $matchingLeads = array_filter($this->traceableIntercomClient->getMergedLeads(), function(array $user) use ($email) {
            return isset($user['email']) && $user['email'] == $email;
        });

        if (count($matchingLeads) == 0) {
            throw new \RuntimeException('No matching merged lead found');
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
     * @Then an intercom tag :tagName should be created for the user :username
     */
    public function anIntercomTagShouldBeCreated($tagName, $username)
    {
        $matchingTags = array_filter(
            $this->traceableIntercomClient->getCreatedTags(),
            function(array $tag) use ($tagName, $username) {
                return $tag['name'] == $tagName && in_array(['user_id' => $username], $tag['users']);
            }
        );

        if (count($matchingTags) != 1) {
            throw new \RuntimeException('No such tag with name found');
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

    /**
     * @param string $username
     *
     * @return array|null
     */
    private function findCreatedOrUpdatedUser($username)
    {
        $matchingUsers = array_filter($this->traceableIntercomClient->getCreatedOrUpdatedUsers(), function(array $user) use ($username) {
            return $user['user_id'] == $username;
        });

        return array_pop($matchingUsers);
    }
}
