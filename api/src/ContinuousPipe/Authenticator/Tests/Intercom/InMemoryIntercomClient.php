<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;

class InMemoryIntercomClient implements IntercomClient
{
    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        if (!array_key_exists('id', $lead)) {
            $lead['id'] = $lead['email'];
        }

        return $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function message(array $message)
    {
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateUser(array $user)
    {
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(array $event)
    {
        return $event;
    }
}
