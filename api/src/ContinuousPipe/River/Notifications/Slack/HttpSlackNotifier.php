<?php

namespace ContinuousPipe\River\Notifications\Slack;

use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\NotificationNotSupported;
use ContinuousPipe\River\Notifications\Notifier;
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
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
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
        $text = '';
        $color = $this->getColorFromStatus($status);

        try {
            $this->httpClient->post($webHookUrl, [
                'attachments' => [
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
            ]);
        } catch (RequestException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getColorFromStatus(Status $status)
    {
        return '#36a64f';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return array_key_exists('slack', $configuration);
    }
}
