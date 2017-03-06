<?php

namespace ContinuousPipe\DevelopmentEnvironment\Status;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\DevelopmentEnvironmentRepository;
use ContinuousPipe\River\Repository\TideRepository as TideAggregateRepository;
use ContinuousPipe\River\Task\Deploy\DeployTask;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\UuidInterface;

class StatusFetcher
{
    /**
     * @var DevelopmentEnvironmentRepository
     */
    private $developmentEnvironmentRepository;
    /**
     * @var TideRepository
     */
    private $tideViewRepository;
    /**
     * @var TideAggregateRepository
     */
    private $tideAggregateRepository;

    public function __construct(
        DevelopmentEnvironmentRepository $developmentEnvironmentRepository,
        TideRepository $tideViewRepository,
        TideAggregateRepository $tideAggregateRepository
    ) {
        $this->developmentEnvironmentRepository = $developmentEnvironmentRepository;
        $this->tideViewRepository = $tideViewRepository;
        $this->tideAggregateRepository = $tideAggregateRepository;
    }

    public function fetch(UuidInterface $environmentUuid) : DevelopmentEnvironmentStatus
    {
        $environment = $this->developmentEnvironmentRepository->find($environmentUuid);
        $status = new DevelopmentEnvironmentStatus();
        if (null === ($token = $environment->getInitializationToken())) {
            return $status->withStatus('TokenNotCreated');
        }

        $tides = $this->tideViewRepository->findByBranch($environment->getFlowUuid(), $token->getGitBranch());
        if (count($tides) == 0) {
            return $status->withStatus('NotStarted');
        }

        /** @var Tide $lastTide */
        $lastTide = current($tides);
        $status = $status->withLastTide($lastTide);

        if ($lastTide->getStatus() == Tide::STATUS_RUNNING) {
            return $status->withStatus('TideRunning');
        } elseif ($lastTide->getStatus() == Tide::STATUS_PENDING) {
            return $status->withStatus('TidePending');
        } elseif ($lastTide->getStatus() == Tide::STATUS_FAILURE || $lastTide->getStatus() == Tide::STATUS_CANCELLED) {
            return $status->withStatus('TideFailed');
        }

        $tide = $this->tideAggregateRepository->find($lastTide->getUuid());

        /** @var DeployTask[] $deployTasks */
        $deployTasks = $tide->getTasks()->ofType(DeployTask::class);
        if (count($deployTasks) == 0) {
            return $status->withStatus('TideFailed');
        }

        foreach ($deployTasks as $deployTask) {
            if (null === ($deployment = $deployTask->getStartedDeployment())) {
                continue;
            }

            $status = $status
                ->withCluster($deployment->getRequest()->getTarget()->getClusterIdentifier())
                ->withEnvironmentName($deployment->getRequest()->getTarget()->getEnvironmentName())
                ->withPublicEndpoints(
                    array_merge($status->getPublicEndpoints(), $deployTask->getPublicEndpoints())
                )
            ;
        }

        return $status->withStatus('Running');
    }
}
