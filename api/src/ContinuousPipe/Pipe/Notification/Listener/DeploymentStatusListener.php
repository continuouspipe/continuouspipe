<?php

namespace ContinuousPipe\Pipe\Notification\Listener;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use ContinuousPipe\Pipe\Notification\HttpNotifier;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class DeploymentStatusListener
{
    /**
     * @var HttpNotifier
     */
    private $httpNotifier;

    /**
     * @var DeploymentLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param HttpNotifier $httpNotifier
     * @param DeploymentLoggerFactory $loggerFactory
     */
    public function __construct(HttpNotifier $httpNotifier, DeploymentLoggerFactory $loggerFactory)
    {
        $this->httpNotifier = $httpNotifier;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param DeploymentEvent $deploymentEvent
     */
    public function notify(DeploymentEvent $deploymentEvent)
    {
        $deployment = $deploymentEvent->getDeployment();
        $callbackUrl = $deployment->getRequest()->getNotificationCallbackUrl();

        if (!empty($callbackUrl)) {
            $logger = $this->loggerFactory->create($deployment);

            try {
                $this->httpNotifier->notify($callbackUrl, $deployment);
                $logger->append(new Text(sprintf('Sent HTTP notification to "%s"', $callbackUrl)));
            } catch (\Exception $e) {
                $logger->append(new Text(sprintf('Error while sending HTTP notification to "%s": %s', $callbackUrl, $e->getMessage())));
            }
        }
    }
}
