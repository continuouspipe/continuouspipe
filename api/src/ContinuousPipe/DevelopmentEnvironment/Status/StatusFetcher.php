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
        if (null === ($token = $environment->getInitializationToken())) {
            return new DevelopmentEnvironmentStatus('TokenNotCreated');
        }

        $tides = $this->tideViewRepository->findByBranch($environment->getFlowUuid(), $token->getGitBranch());
        if (count($tides) == 0) {
            return new DevelopmentEnvironmentStatus('NotStarted');
        }

        /** @var Tide $lastTide */
        $lastTide = current($tides);
        if ($lastTide->getStatus() == Tide::STATUS_RUNNING) {
            return new DevelopmentEnvironmentStatus('TideRunning');
        } elseif ($lastTide->getStatus() == Tide::STATUS_PENDING) {
            return new DevelopmentEnvironmentStatus('TidePending');
        } elseif ($lastTide->getStatus() == Tide::STATUS_FAILURE || $lastTide->getStatus() == Tide::STATUS_CANCELLED) {
            return new DevelopmentEnvironmentStatus('TideFailed');
        }

        $tide = $this->tideAggregateRepository->find($lastTide->getUuid());

        /** @var DeployTask[] $deployTasks */
        $deployTasks = $tide->getTasks()->ofType(DeployTask::class);
        if (count($deployTasks) == 0) {
            return new DevelopmentEnvironmentStatus('NoDeploymentFound');
        }

        $cluster = null;
        $publicEndpoints = [];

        foreach ($deployTasks as $deployTask) {
            $cluster = $deployTask->getConfiguration()->getClusterIdentifier();
            $publicEndpoints = array_merge($publicEndpoints, $deployTask->getPublicEndpoints());
        }

        return new DevelopmentEnvironmentStatus('Running', $cluster, $publicEndpoints);
    }
}
