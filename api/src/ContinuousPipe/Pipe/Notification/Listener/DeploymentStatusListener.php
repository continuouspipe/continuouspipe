<?php

namespace ContinuousPipe\Pipe\Notification\Listener;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Notification\HttpNotifier;

class DeploymentStatusListener
{
    /**
     * @var HttpNotifier
     */
    private $httpNotifier;

    /**
     * @param HttpNotifier $httpNotifier
     */
    public function __construct(HttpNotifier $httpNotifier)
    {
        $this->httpNotifier = $httpNotifier;
    }

    /**
     * @param DeploymentEvent $deploymentEvent
     */
    public function notify(DeploymentEvent $deploymentEvent)
    {
        $deployment = $deploymentEvent->getDeployment();
        $callbackUrl = $deployment->getRequest()->getNotificationCallbackUrl();

        if (!empty($callbackUrl)) {
            $this->httpNotifier->notify($callbackUrl, $deployment);
        }
    }
}
