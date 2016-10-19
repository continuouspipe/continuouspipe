<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\CommandBus\DelayedCommandBus;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;
use ContinuousPipe\River\Command\DeleteEnvironments;

class DeleteEnvironmentsHandler
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @var ClusterIdentifierResolver
     */
    private $clusterIdentifierResolver;

    /**
     * @var LoggerInterface
     */
    private $systemLogger;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DelayedCommandBus
     */
    private $delayedCommandBus;

    /**
     * @var int
     */
    private $retryInterval;

    /**
     * @param Client                    $client
     * @param TideRepository            $tideRepository
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     * @param ClusterIdentifierResolver $clusterIdentifierResolver
     * @param LoggerInterface           $systemLogger
     * @param EventStore                $eventStore
     * @param DelayedCommandBus         $delayedCommandBus
     * @param int                       $retryInterval
     */
    public function __construct(Client $client, TideRepository $tideRepository, EnvironmentNamingStrategy $environmentNamingStrategy, ClusterIdentifierResolver $clusterIdentifierResolver, LoggerInterface $systemLogger, EventStore $eventStore, DelayedCommandBus $delayedCommandBus, $retryInterval)
    {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->systemLogger = $systemLogger;
        $this->eventStore = $eventStore;
        $this->delayedCommandBus = $delayedCommandBus;
        $this->retryInterval = $retryInterval;
    }

    /**
     * @param DeleteEnvironments $command
     */
    public function handle(DeleteEnvironments $command)
    {
        $tides = $this->tideRepository->findByCodeReference(
            $command->getFlowUuid(),
            $command->getCodeReference()
        );

        if ($this->oneOfTheTidesHasStatus($tides, [Tide::STATUS_PENDING, Tide::STATUS_RUNNING])) {
            $this->delayedCommandBus->publish($command, $this->retryInterval);

            return;
        }

        foreach ($tides as $tide) {
            $targets = $this->getTideTargets($tide);

            foreach ($targets as $target) {
                try {
                    $this->client->deleteEnvironment($target, $tide->getTeam(), $tide->getUser());
                } catch (\Exception $e) {
                    $this->systemLogger->warning($e->getMessage(), [
                        'target' => $target,
                    ]);
                }
            }
        }
    }

    /**
     * @param Tide $tide
     *
     * @return Client\DeploymentRequest\Target[]
     */
    private function getTideTargets(Tide $tide)
    {
        return array_map(function (DeploymentStarted $deploymentStarted) {
            return $deploymentStarted->getDeployment()->getRequest()->getTarget();
        }, $this->getStartedDeployments($tide));
    }

    /**
     * @param Tide $tide
     *
     * @return DeploymentStarted[]
     */
    private function getStartedDeployments(Tide $tide)
    {
        return $this->eventStore->findByTideUuidAndType($tide->getUuid(), DeploymentStarted::class);
    }

    /**
     * @param Tide[]   $tides
     * @param string[] $statuses
     *
     * @return bool
     */
    private function oneOfTheTidesHasStatus(array $tides, array $statuses)
    {
        foreach ($tides as $tide) {
            if (in_array($tide->getStatus(), $statuses)) {
                return true;
            }
        }

        return false;
    }
}
