<?php

namespace ContinuousPipe\Pipe\Listener\DeploymentStarted;

use ContinuousPipe\HealthChecker\HealthCheckerClient;
use ContinuousPipe\HealthChecker\HealthCheckerException;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\View\Deployment;
use LogStream\Exception as LogStreamException;
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
     * @param DeploymentStarted $event
     */
    public function notify(DeploymentStarted $event)
    {
        $context = $event->getDeploymentContext();
        try {
            $problems = $this->healthChecker->findProblems($context->getCluster());
            if (0 === count($problems)) {
                return;
            }
            $logger = $this->createNode(count($problems));
            foreach ($problems as $problem) {
                $logger->child(new Text($problem->getMessage()));
            }
        } catch (HealthCheckerException $e) {
            $this->logger->warning(
                'Can\'t get the cluster problems',
                ['exception' => $e]
            );
        } catch (LogStreamException $e) {
            $this->logger->warning(
                'Can\'t log to logstream',
                ['exception' => $e]
            );
        }
    }

    private function createNode(int $numOfProblems)
    {
        $logger = $this->loggerFactory->create();
        $logger->child(new Text(sprintf(
            'Found %d %s with the cluster',
            $numOfProblems,
            $numOfProblems == 1 ? 'problem' : 'problems'
        )))->updateStatus(Log::FAILURE);

        return $logger;
    }
}
