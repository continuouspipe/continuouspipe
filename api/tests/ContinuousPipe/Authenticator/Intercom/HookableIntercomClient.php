<?php

namespace ContinuousPipe\Authenticator\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;

class HookableIntercomClient implements IntercomClient
{
    /**
     * @var IntercomClient
     */
    private $decoratedClient;

    /**
     * @var callable[]
     */
    private $hooks = [];

    public function __construct(IntercomClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        return $this->decoratedClient->createLead($lead);
    }

    /**
     * {@inheritdoc}
     */
    public function message(array $message)
    {
        return $this->decoratedClient->message($message);
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateUser(array $user)
    {
        return $this->decoratedClient->createOrUpdateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(array $event)
    {
        return $this->decoratedClient->createEvent($event);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeLeadIfExists(array $lead, array $user)
    {
        return $this->decoratedClient->mergeLeadIfExists($lead, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function tagUsers(string $name, array $users, int $id = null)
    {
        foreach ($this->hooks as $hook) {
            $hook();
        }

        return $this->decoratedClient->tagUsers($name, $users, $id);
    }

    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
