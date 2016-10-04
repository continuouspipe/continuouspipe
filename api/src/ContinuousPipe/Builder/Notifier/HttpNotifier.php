<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\HttpNotification;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
     * @param Notification $notification
     * @param Build            $build
     *
     * @throws NotificationException
     */
    public function notify(Notification $notification, Build $build)
    {
        if ($http = $notification->getHttp()) {
            {
                try {
                    $this->httpClient->post($http->getAddress(), [
                        'body' => $this->serializer->serialize($build, 'json'),
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                    ]);
                } catch (RequestException $e) {
                    throw new NotificationException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
    }
}
