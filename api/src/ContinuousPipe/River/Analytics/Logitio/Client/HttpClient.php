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
    /**
     * @var string
     */
    private $logitioUrl;
    /**
     * @var string
     */
    private $logitioApiKey;

    public function __construct(Client $client, LoggerInterface $logger, string $logitioUrl, string  $logitioApiKey)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->logitioUrl = $logitioUrl;
        $this->logitioApiKey = $logitioApiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($logType, array $event)
    {
        try {
            $this->client->post($this->logitioUrl, [
                'json' => $event,
                'headers' => [
                    'ApiKey' => $this->logitioApiKey,
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
