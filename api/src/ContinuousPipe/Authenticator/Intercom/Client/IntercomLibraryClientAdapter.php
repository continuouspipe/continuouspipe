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
        return $this->client->leads->create($lead);
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
}
