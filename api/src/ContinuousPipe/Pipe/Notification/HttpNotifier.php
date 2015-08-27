<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use ContinuousPipe\Pipe\View\Deployment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\Serializer;
use LogStream\Node\Text;

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
     * @var DeploymentLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Client                  $httpClient
     * @param Serializer              $serializer
     * @param DeploymentLoggerFactory $loggerFactory
     */
    public function __construct(Client $httpClient, Serializer $serializer, DeploymentLoggerFactory $loggerFactory)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
        $logger = $this->loggerFactory->create($deployment);
        if (empty($address)) {
            $logger->append(new Text('Empty callback, not sending HTTP notification'));

            return;
        }

        $logger->append(new Text(sprintf(
            'Sending HTTP notification back "%s"',
            $address
        )));

        try {
            $this->httpClient->post($address, [
                'body' => $this->serializer->serialize($deployment, 'json'),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (RequestException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
