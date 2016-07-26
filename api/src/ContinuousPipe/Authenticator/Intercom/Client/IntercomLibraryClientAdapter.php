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
     * @param string $applicationIdentifier
     * @param string $apiKey
     */
    public function __construct($applicationIdentifier, $apiKey)
    {
        $this->client = new IntercomLibraryClient($applicationIdentifier, $apiKey);
    }

    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        return $this->client->leads->create($lead);
    }
}
