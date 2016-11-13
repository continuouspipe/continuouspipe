<?php

namespace ContinuousPipe\River\Notifications\Slack;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\NotificationNotSupported;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Pipe\PublicEndpoint\PublicEndpointWriter;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpSlackNotifier implements Notifier
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var PublicEndpointWriter
     */
    private $publicEndpointWriter;

    /**
     * @param Client               $httpClient
     * @param PublicEndpointWriter $publicEndpointWriter
     */
    public function __construct(Client $httpClient, PublicEndpointWriter $publicEndpointWriter)
    {
        $this->httpClient = $httpClient;
        $this->publicEndpointWriter = $publicEndpointWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (!array_key_exists('slack', $configuration)) {
            throw new NotificationNotSupported('This notifier only supports Slack notifications');
        }

        $webHookUrl = $configuration['slack']['webhook_url'];

        $title = $status->getDescription();
        $text = $this->getNotificationDescription($status);
        $color = $this->getColorFromStatus($status);

        try {
            $this->httpClient->post($webHookUrl, [
                'json' => [
                    'attachments' => [
                        [
                            'fallback' => $title,
                            'color' => $color,
                            'author_name' => 'ContinuousPipe',
                            'author_link' => 'https://continuouspipe.io',
                            'author_icon' => 'https://continuouspipe.io/logo.png',
                            'title' => $title,
                            'title_link' => $status->getUrl(),
                            'text' => $text,
                            'mrkdwn_in' => ['text'],
                            'fields' => [
                                [
                                    'title' => 'Branch',
                                    'value' => $tide->getCodeReference()->getBranch(),
                                    'short' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (RequestException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param Status $status
     *
     * @return string
     */
    private function getColorFromStatus(Status $status)
    {
        if ($status->getState() == Status::STATE_SUCCESS) {
            return '#36a64f';
        } elseif ($status->getState() == Status::STATE_FAILURE) {
            return '#e01765';
        }

        return '#70CADB';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return array_key_exists('slack', $configuration);
    }

    /**
     * @param Status $status
     *
     * @return string
     */
    private function getNotificationDescription(Status $status)
    {
        $endpoints = $status->getPublicEndpoints();
        if (count($endpoints) == 0) {
            return 'No public endpoint available';
        }

        return implode("\n", array_map(function (PublicEndpoint $publicEndpoint) {
            return sprintf('*%s*: %s', $publicEndpoint->getName(), $this->publicEndpointWriter->writeAddress($publicEndpoint));
        }, $endpoints));
    }
}
