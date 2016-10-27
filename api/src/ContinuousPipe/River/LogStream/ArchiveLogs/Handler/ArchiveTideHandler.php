<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Handler;

use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveTideCommand;
use ContinuousPipe\River\LogStream\ArchiveLogs\Event\TideLogsArchived;
use LogStream\Client;
use LogStream\Tree\TreeLog;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class ArchiveTideHandler
{
    /**
     * @var Client
     */
    private $logStreamClient;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client          $logStreamClient
     * @param MessageBus      $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(Client $logStreamClient, MessageBus $eventBus, LoggerInterface $logger)
    {
        $this->logStreamClient = $logStreamClient;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    /**
     * @param ArchiveTideCommand $command
     */
    public function handle(ArchiveTideCommand $command)
    {
        try {
            $this->logStreamClient->archive(TreeLog::fromId($command->getLogId()));
            $this->eventBus->handle(new TideLogsArchived($command->getTideUuid()));
        } catch (Client\ClientException $e) {
            if ($e->getMessage() == 'Found status 404') {
                $this->eventBus->handle(new TideLogsArchived($command->getTideUuid()));
            } else {
                $this->logger->warning('Unable to archive tide', [
                    'message' => $e->getMessage(),
                    'tideUuid' => (string) $command->getTideUuid(),
                    'logId' => $command->getLogId(),
                    'exception' => $e,
                ]);
            }
        }
    }
}
