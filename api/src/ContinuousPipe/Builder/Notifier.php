<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Notifier\HttpNotifier;
use ContinuousPipe\Builder\Notifier\NotificationException;
use ContinuousPipe\LogStream\Log;
use ContinuousPipe\LogStream\LoggerFactory;

class Notifier
{
    /**
     * @var HttpNotifier
     */
    private $httpNotifier;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param LoggerFactory $loggerFactory
     * @param HttpNotifier $httpNotifier
     */
    public function __construct(LoggerFactory $loggerFactory, HttpNotifier $httpNotifier)
    {
        $this->httpNotifier = $httpNotifier;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param Notification $notification
     * @param Build $build
     */
    public function notify(Notification $notification, Build $build)
    {
        if ($http = $notification->getHttp()) {
            $logger = $this->loggerFactory->createLogger($build);

            try {
                $this->httpNotifier->notify($http, $build);
                $logger->log(Log::output(sprintf('Sent HTTP notification to "%s"', $http->getAddress())));
            } catch (NotificationException $e) {
                $logger->log(Log::exception($e));
            }
        }
    }
}
