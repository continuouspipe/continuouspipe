<?php

namespace ContinuousPipe\Pipe\Notification\Listener;

use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use ContinuousPipe\Pipe\Notification\HttpNotifier;
use ContinuousPipe\Pipe\View\DeploymentRepository;
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
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param HttpNotifier            $httpNotifier
     * @param DeploymentLoggerFactory $loggerFactory
     * @param DeploymentRepository    $deploymentRepository
     */
    public function __construct(HttpNotifier $httpNotifier, DeploymentLoggerFactory $loggerFactory, DeploymentRepository $deploymentRepository)
    {
        $this->httpNotifier = $httpNotifier;
        $this->loggerFactory = $loggerFactory;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentEvent $event)
    {
        $deployment = $this->deploymentRepository->find($event->getDeploymentUuid());
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
