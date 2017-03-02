<?php

namespace ContinuousPipe\Pipe\Listener;

use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Logging\DeploymentLoggerFactory;
use LogStream\Node\Text;

class ClusterAlertListener
{
    private $loggerFactory;

    public function __construct(DeploymentLoggerFactory $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentStarted $event)
    {
        $context = $event->getDeploymentContext();
        $clusterCredentials = $context->getCluster();

        $logger = $this->loggerFactory->create($context->getDeployment());
        $logger->child(new Text('test'));
    }
}
