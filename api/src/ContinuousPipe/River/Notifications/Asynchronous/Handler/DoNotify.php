<?php

namespace ContinuousPipe\River\Notifications\Asynchronous\Handler;

use ContinuousPipe\River\Notifications\Asynchronous\Command\NotifyCommand;
use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class DoNotify
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Notifier $notifier,
        TideRepository $tideRepository,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger
    ) {
        $this->notifier = $notifier;
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    public function handle(NotifyCommand $command)
    {
        try {
            $tide = $this->getTide($command);
        } catch (TideNotFound $e) {
            $this->logger->warning('Handling notification failed, because tide does not exist.', [
                'message' => $e->getMessage(),
                'tide' => (string) $command->getTideUuid(),
                'exception' => $e,
            ]);
            return;
        }

        try {
            $status = $command->getStatus();
            $configuration = $command->getConfiguration();
            $this->notifier->notify($tide, $status, $configuration);
        } catch (NotificationException $e) {
            $logger = $this->loggerFactory->fromId($tide->getLogId());
            $logger->child(new Text($e->getMessage()));

            $this->logger->warning('Unable to send notification', [
                'message' => $e->getMessage(),
                'notification' => $command->getConfiguration(),
                'tide' => (string) $command->getTideUuid(),
                'exception' => $e,
            ]);
        }
    }

    /**
     * @param NotifyCommand $command
     * @return Tide
     * @throws TideNotFound
     */
    private function getTide(NotifyCommand $command): Tide
    {
        return $this->tideRepository->find($command->getTideUuid());
    }
}
