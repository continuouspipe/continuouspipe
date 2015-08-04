<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Notifier\HttpNotifier;
use ContinuousPipe\Builder\Notifier\NotificationException;
use LogStream\Logger;
use LogStream\LoggerFactory;
use LogStream\Node\Container;
use LogStream\Node\Text;
use LogStream\WrappedLog;

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
     * @param HttpNotifier  $httpNotifier
     */
    public function __construct(LoggerFactory $loggerFactory, HttpNotifier $httpNotifier)
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
            $logger = $this->getLogger($build);

            try {
                $this->httpNotifier->notify($http, $build);
                $logger->append(new Text(sprintf('Sent HTTP notification to "%s"', $http->getAddress())));
            } catch (NotificationException $e) {
                $logger->append(new Text($e));
            }
        }
    }

    /**
     * Get logger for that given build.
     *
     * @param Build $build
     *
     * @return Logger
     */
    private function getLogger(Build $build)
    {
        $logging = $build->getRequest()->getLogging();

        if ($logStream = $logging->getLogstream()) {
            return $this->loggerFactory->from(
                new WrappedLog($logStream->getParentLogIdentifier(), new Container())
            );
        }

        return;
    }
}
