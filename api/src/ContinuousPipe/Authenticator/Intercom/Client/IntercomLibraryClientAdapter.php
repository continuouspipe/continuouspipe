<?php

namespace ContinuousPipe\Authenticator\Intercom\Client;

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
        return $this->stdClassToArray(
            $this->client->leads->create($lead)
        );
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

        return $this->client->messages->create($message);
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateUser(array $user)
    {
        return $this->stdClassToArray(
            $this->client->users->create($user)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createEvent(array $event)
    {
        return $this->stdClassToArray(
            $this->client->events->create($event)
        );
    }

    /**
     * @param \stdClass $object
     *
     * @return array
     */
    private function stdClassToArray(\stdClass $object)
    {
        return json_decode(json_encode($object), true);
    }
}
