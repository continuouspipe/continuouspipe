<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\View\Deployment;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

class HttpNotifier implements Notifier
{
    /**
     * @var Client
     */
    private $httpClient;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Client     $httpClient
     * @param Serializer $serializer
     */
    public function __construct(Client $httpClient, Serializer $serializer)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
        $this->httpClient->post($address, [
            'body' => $this->serializer->serialize($deployment, 'json'),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
