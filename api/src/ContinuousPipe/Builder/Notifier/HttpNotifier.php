<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\HttpNotification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\Serializer;

class HttpNotifier
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
     * @param Client $httpClient
     * @param Serializer $serializer
     */
    public function __construct(Client $httpClient, Serializer $serializer)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
    }

    /**
     * @param HttpNotification $http
     * @param Build $build
     * @return \GuzzleHttp\Message\ResponseInterface
     * @throws NotificationException
     */
    public function notify(HttpNotification $http, Build $build)
    {
        try {
            return $this->httpClient->post($http->getAddress(), [
                'body' => $this->serializer->serialize($build, 'json'),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (RequestException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
