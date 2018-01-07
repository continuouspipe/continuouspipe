<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class ViewRepositoryBasedConcurrencyManager implements TideConcurrencyManager
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var int
     */
    private $retryStartInterval;

    /**
     * @param TideRepository    $tideRepository
     * @param int               $retryStartInterval
     */
    public function __construct(TideRepository $tideRepository, $retryStartInterval)
    {
        $this->tideRepository = $tideRepository;
        $this->retryStartInterval = $retryStartInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function tideStartRecommendation(Tide $tide) : StartingTideRecommendation
    {
        $runningTides = $this->tideRepository->findRunningByFlowUuidAndBranch(
            $tide->getFlowUuid(),
            $tide->getCodeReference()->getBranch()
        );

        if (count($runningTides) > 0) {
            return StartingTideRecommendation::postponeTo(
                (new \DateTime())->add(new \DateInterval('PT'.$this->retryStartInterval.'S')),
                sprintf('%d already running tide for this branch', count($runningTides))
            );
        }

        return StartingTideRecommendation::runNow();
    }
}
