<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use ContinuousPipe\Builder\Notifier\HttpNotifier;
use ContinuousPipe\Builder\Notifier\NotificationException;
use LogStream\Log;
use LogStream\Node\Text;

class Notifier
{
    /**
     * @var HttpNotifier
     */
    private $httpNotifier;

    /**
     * @var BuildLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param BuildLoggerFactory $loggerFactory
     * @param HttpNotifier       $httpNotifier
     */
    public function __construct(BuildLoggerFactory $loggerFactory, HttpNotifier $httpNotifier)
    {
        $this->httpNotifier = $httpNotifier;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param Notification $notification
     * @param Build        $build
     */
    public function notify(Notification $notification, Build $build)
    {
        if ($http = $notification->getHttp()) {
            $logger = $this->loggerFactory->forBuild($build);

            try {
                $this->httpNotifier->notify($http, $build);
            } catch (NotificationException $e) {
                $logger->child(new Text($e->getMessage()))->updateStatus(Log::FAILURE);
            }
        }
    }
}
