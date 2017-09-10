<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Pipe\ClusterIdentifierResolver;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName\EnvironmentNamingStrategy;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;
use ContinuousPipe\River\Command\DeleteEnvironments;
use SimpleBus\Message\Bus\MessageBus;

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
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var int
     */
    private $retryInterval;

    public function __construct(
        Client $client,
        TideRepository $tideRepository,
        EnvironmentNamingStrategy $environmentNamingStrategy,
        ClusterIdentifierResolver $clusterIdentifierResolver,
        LoggerInterface $systemLogger,
        EventStore $eventStore,
        MessageBus $commandBus,
        int $retryInterval
    ) {
        $this->client = $client;
        $this->tideRepository = $tideRepository;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
        $this->clusterIdentifierResolver = $clusterIdentifierResolver;
        $this->systemLogger = $systemLogger;
        $this->eventStore = $eventStore;
        $this->commandBus = $commandBus;
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
            $this->commandBus->handle(new DeleteEnvironments(
                $command->getFlowUuid(),
                $command->getCodeReference(),
                (new \DateTime())->add(new \DateInterval('PT'.$this->retryInterval.'S'))
            ));

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
