<?php

namespace ContinuousPipe\Authenticator\Intercom\Client;

use GuzzleHttp\Exception\RequestException;
use Intercom\IntercomClient as IntercomLibraryClient;

class IntercomLibraryClientAdapter implements IntercomClient
{
    /**
     * @var IntercomLibraryClient
     */
    private $client;

    /**
     * @var string
     */
    private $defaultAdminIdentifier;

    /**
     * @param string $applicationIdentifier
     * @param string $apiKey
     * @param string $defaultAdminIdentifier
     */
    public function __construct($applicationIdentifier, $apiKey, $defaultAdminIdentifier)
    {
        $this->client = new IntercomLibraryClient($applicationIdentifier, $apiKey);
        $this->defaultAdminIdentifier = $defaultAdminIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        try {
            return $this->stdClassToArray(
                $this->client->leads->create($lead)
            );
        } catch (RequestException $e) {
            throw new IntercomException('Unable to create a lead', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function message(array $message)
    {
        if (!array_key_exists('from', $message)) {
            $message['from'] = [
                'type' => 'admin',
                'id' => $this->defaultAdminIdentifier,
            ];
        }

        try {
            return $this->client->messages->create($message);
        } catch (RequestException $e) {
            throw new IntercomException('Unable to send a message', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateUser(array $user)
    {
        try {
            return $this->stdClassToArray(
                $this->client->users->create($user)
            );
        } catch (RequestException $e) {
            throw new IntercomException('Unable to create or update a user', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(array $event)
    {
        if (!array_key_exists('created_at', $event)) {
            $event['created_at'] = time();
        }

        try {
            return $this->stdClassToArray(
                $this->client->events->create($event)
            );
        } catch (RequestException $e) {
            throw new IntercomException('Unable to create an event', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mergeLeadIfExists(array $lead, array $user)
    {
        try {
            return $this->stdClassToArray(
                $this->client->leads->convertLead([
                    'contact' => $lead,
                    'user' => $user,
                ])
            );
        } catch (RequestException $e) {
            throw new IntercomException('Unable to merge lead', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tagUsers(string $name, array $users, int $id = null)
    {
        try {
            $options = [
                'name'  => $name,
                'users' => $users,
            ];

            if (!is_null($id)) {
                $options['id'] = $id;
            }

            return $this->stdClassToArray($this->client->tags->tag($options));
        } catch (RequestException $e) {
            throw new IntercomException('Unable to add tag', $e->getCode(), $e);
        }
    }

    /**
     * @param \stdClass $object
     *
     * @return array
     */
    private function stdClassToArray(\stdClass $object = null)
    {
        if (null === $object) {
            return null;
        }

        return json_decode(json_encode($object), true);
    }
}
