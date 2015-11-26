<?php

namespace ContinuousPipe\River\Analytics\Keen\Client;

use KeenIO\Client\KeenIOClient;
use Psr\Log\LoggerInterface;

class KeenClientFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var string
     */
    private $writeKey;

    /**
     * @param LoggerInterface $logger
     * @param string          $projectId
     * @param string          $writeKey
     */
    public function __construct(LoggerInterface $logger, $projectId = null, $writeKey = null)
    {
        $this->logger = $logger;
        $this->projectId = $projectId;
        $this->writeKey = $writeKey;
    }

    /**
     * @return KeenClient
     */
    public function create()
    {
        if ($this->projectId === null) {
            $this->logger->debug('Found NULL project ID, unable to create Keen client, using void one');

            return new VoidClient();
        }

        return new HttpClient(KeenIOClient::factory([
            'projectId' => $this->projectId,
            'writeKey' => $this->writeKey,
        ]), $this->logger);
    }
}
