<?php

namespace ContinuousPipe\River\Analytics\Logitio\Client;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class HttpClient implements LogitioClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $client, LoggerInterface $logger, string $logitioUrl, string  $logitioApiKey)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($logType, array $event)
    {
        try {
            $this->client->post('https://api.logit.io/v2', [
                'json' => $event,
                'headers' => [
                    'ApiKey' => '56efc8a1-541b-43d2-a157-808b3660b8a5',
                    'LogType' => $logType
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->critical(
                'Unable to log tide to Logitio',
                [
                    'message' => $e->getMessage(),
                ]
            );
        }
    }
}
