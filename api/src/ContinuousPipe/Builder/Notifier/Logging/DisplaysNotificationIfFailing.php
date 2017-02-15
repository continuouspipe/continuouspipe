<?php

namespace ContinuousPipe\Builder\Notifier\Logging;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class DisplaysNotificationIfFailing implements Notifier
{
    /**
     * @var Notifier
     */
    private $decoratedNotifier;

    /**
     * @var BuildLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Notifier           $decoratedNotifier
     * @param BuildLoggerFactory $loggerFactory
     */
    public function __construct(Notifier $decoratedNotifier, BuildLoggerFactory $loggerFactory)
    {
        $this->decoratedNotifier = $decoratedNotifier;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Notification $notification, Build $build)
    {
        try {
            $this->decoratedNotifier->notify($notification, $build);
        } catch (Notifier\NotificationException $e) {
            $logger = $this->loggerFactory->forBuild($build);
            $logger->child(new Text('Notification failed. '.$e->getMessage()))->updateStatus(Log::FAILURE);
        }
    }
}
