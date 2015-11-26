<?php

namespace ContinuousPipe\River\Analytics\Keen\Client;

use KeenIO\Client\KeenIOClient;
use Psr\Log\LoggerInterface;

class HttpClient implements KeenClient
{
    /**
     * @var KeenIOClient
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param KeenIOClient    $client
     * @param LoggerInterface $logger
     */
    public function __construct(KeenIOClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($collection, array $event)
    {
        try {
            return $this->client->addEvent($collection, $event);
        } catch (\Exception $e) {
            $this->logger->critical('Unable to log tide to Keen', [
                'message' => $e->getMessage(),
            ]);
        }

        return;
    }
}
