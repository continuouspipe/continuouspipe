<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;

class TraceableIntercomClient implements IntercomClient
{
    /**
     * @var IntercomClient
     */
    private $decoratedClient;

    /**
     * @var array[]
     */
    private $createdLeads = [];

    /**
     * @var array[]
     */
    private $sentMessages = [];

    /**
     * @var array[]
     */
    private $createdOrUpdatedUsers = [];

    /**
     * @var array[]
     */
    private $createdEvents = [];

    /**
     * @param IntercomClient $decoratedClient
     */
    public function __construct(IntercomClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        $created = $this->decoratedClient->createLead($lead);

        $this->createdLeads[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateUser(array $user)
    {
        $createdOrUpdatedUser = $this->decoratedClient->createOrUpdateUser($user);

        $this->createdOrUpdatedUsers[] = $createdOrUpdatedUser;

        return $createdOrUpdatedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function message(array $message)
    {
        $sent = $this->decoratedClient->message($message);

        $this->sentMessages[] = $message;

        return $sent;
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(array $event)
    {
        $created = $this->decoratedClient->createEvent($event);

        $this->createdEvents[] = $created;

        return $created;
    }

    /**
     * @return array[]
     */
    public function getCreatedLeads()
    {
        return $this->createdLeads;
    }

    /**
     * @return \array[]
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * @return \array[]
     */
    public function getCreatedOrUpdatedUsers()
    {
        return $this->createdOrUpdatedUsers;
    }

    /**
     * @return \array[]
     */
    public function getCreatedEvents()
    {
        return $this->createdEvents;
    }
}
