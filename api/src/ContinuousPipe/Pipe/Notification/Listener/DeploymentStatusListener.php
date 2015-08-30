<?php

namespace ContinuousPipe\Pipe\Notification\Listener;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use ContinuousPipe\Pipe\Notification\NotificationException;
use ContinuousPipe\Pipe\Notification\Notifier;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use LogStream\Node\Text;

class DeploymentStatusListener
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var DeploymentLoggerFactory
     */
    private $loggerFactory;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param Notifier                $notifier
     * @param DeploymentLoggerFactory $loggerFactory
     * @param DeploymentRepository    $deploymentRepository
     */
    public function __construct(Notifier $notifier, DeploymentLoggerFactory $loggerFactory, DeploymentRepository $deploymentRepository)
    {
        $this->notifier = $notifier;
        $this->loggerFactory = $loggerFactory;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentEvent $event)
    {
        $deployment = $this->deploymentRepository->find($event->getDeploymentUuid());
        $logger = $this->loggerFactory->create($deployment);

        if (null === ($notification = $deployment->getRequest()->getNotification())) {
            $logger->append(new Text('No notification configuration'));

            return;
        }

        $httpCallbackUrl = $notification->getHttpCallbackUrl();
        if (empty($httpCallbackUrl)) {
            $logger->append(new Text('Empty HTTP notification URL'));

            return;
        }

        try {
            $this->notifier->notify($httpCallbackUrl, $deployment);
        } catch (NotificationException $e) {
            $logger->append(new Text(sprintf('Error while sending notification to "%s": %s', $httpCallbackUrl, $e->getMessage())));
        }
    }
}
