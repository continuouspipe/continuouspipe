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

    /**
     * {@inheritdoc}
     */
    public function mergeLeadIfExists(array $lead, array $user)
    {
        return array_merge($lead, $user);
    }

    /**
     * @param string $name
     * @param array $users
     * @param int $id
     *
     * @return array
     */
    public function tagUsers(string $name, array $users, int $id = null)
    {
        return ['name' => $name, 'users' => $users, 'id' => $id];
    }
}
