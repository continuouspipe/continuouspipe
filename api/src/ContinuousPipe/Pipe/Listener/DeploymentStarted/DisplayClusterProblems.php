<?php

namespace ContinuousPipe\Pipe\Listener\DeploymentStarted;

use ContinuousPipe\HealthChecker\HealthCheckerClient;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\View\Deployment;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Container;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

class DisplayClusterProblems
{
    private $loggerFactory;
    private $healthChecker;
    private $logger;

    public function __construct(LoggerFactory $loggerFactory, HealthCheckerClient $healthChecker, LoggerInterface $logger)
    {
        $this->loggerFactory = $loggerFactory;
        $this->healthChecker = $healthChecker;
        $this->logger = $logger;
    }

    /**
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentStarted $event)
    {
        $context = $event->getDeploymentContext();
        try {
            $problems = $this->healthChecker->findProblems($context->getCluster());
            if (count($problems)) {
                $clusterProblemsLog = $this->createNode(count($problems));
                foreach ($problems as $problem) {
                    $logger = $this->loggerFactory->from($clusterProblemsLog);
                    $logger->child(new Text($problem->getMessage()));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function createNode(int $numOfProblems)
    {
        $logger = $this->loggerFactory->create();
        $log = new Text(
            sprintf(
                'Found %d %s with the cluster',
                $numOfProblems,
                $numOfProblems == 1 ? 'problem' : 'problems'
            )
        );
        $clusterProblemsLog = $logger->child($log);
        $clusterProblemsLog->updateStatus(Log::FAILURE);
        return $clusterProblemsLog->getLog();
    }
}
