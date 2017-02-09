<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\View\Tide;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class TideStatusListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * @var Notifier
     */
    private $notifier;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TideRepository  $tideRepository
     * @param StatusFactory   $statusFactory
     * @param LoggerFactory   $loggerFactory
     * @param Notifier        $notifier
     * @param LoggerInterface $logger
     */
    public function __construct(TideRepository $tideRepository, StatusFactory $statusFactory, LoggerFactory $loggerFactory, Notifier $notifier, LoggerInterface $logger)
    {
        $this->tideRepository = $tideRepository;
        $this->loggerFactory = $loggerFactory;
        $this->statusFactory = $statusFactory;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $this->triggerNotifications(
            $this->tideRepository->find($event->getTideUuid())
        );
    }

    /**
     * @param Tide $tide
     */
    public function triggerNotifications(Tide $tide)
    {
        $status = $this->statusFactory->createFromTideAndEvent($tide);
        $notifications = $this->findNotifications($tide);

        foreach ($notifications as $notification) {
            try {
                $this->notifier->notify($tide, $status, $notification);
            } catch (NotificationException $e) {
                $logger = $this->loggerFactory->fromId($tide->getLogId());
                $logger->child(new Text($e->getMessage()));

                $this->logger->warning('Unable to send notification', [
                    'message' => $e->getMessage(),
                    'notification' => $notification,
                    'flow' => (string) $tide->getFlowUuid(),
                    'exception' => $e,
                ]);
            }
        }
    }

    /**
     * @param Tide $tide
     *
     * @return array
     */
    private function findNotifications(Tide $tide)
    {
        $configuration = $tide->getConfiguration();

        if (!array_key_exists('notifications', $configuration)) {
            return [];
        }

        return $configuration['notifications'];
    }
}
