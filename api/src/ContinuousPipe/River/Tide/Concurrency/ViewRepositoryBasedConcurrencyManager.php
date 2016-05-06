<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;
use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class ViewRepositoryBasedConcurrencyManager implements TideConcurrencyManager
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var DelayedCommandBus
     */
    private $delayedCommandBus;

    /**
     * @var int
     */
    private $retryStartInterval;

    /**
     * @param TideRepository    $tideRepository
     * @param DelayedCommandBus $delayedCommandBus
     * @param int               $retryStartInterval
     */
    public function __construct(TideRepository $tideRepository, DelayedCommandBus $delayedCommandBus, $retryStartInterval = 60000)
    {
        $this->tideRepository = $tideRepository;
        $this->delayedCommandBus = $delayedCommandBus;
        $this->retryStartInterval = $retryStartInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldTideStart(Tide $tide)
    {
        $runningTides = $this->tideRepository->findRunningByFlowUuidAndBranch(
            $tide->getFlow()->getUuid(),
            $tide->getCodeReference()->getBranch()
        );

        return count($runningTides) == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function postPoneTideStart(Tide $tide)
    {
        $this->delayedCommandBus->publish(
            new RunPendingTidesCommand($tide->getFlow()->getUuid(), $tide->getCodeReference()->getBranch()),
            $this->retryStartInterval
        );
    }
}
