<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;
use ContinuousPipe\Authenticator\Intercom\Client\IntercomException;

class InMemoryIntercomClient implements IntercomClient
{
    private $users = [];

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
        $this->users[$user['user_id']] = $user;

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
     * {@inheritdoc}
     */
    public function tagUsers(string $name, array $users, int $id = null)
    {
        foreach ($users as $user) {
            if (!array_key_exists($user['user_id'], $this->users)) {
                throw new IntercomException('User not found');
            }
        }

        return ['name' => $name, 'users' => $users, 'id' => $id];
    }
}
