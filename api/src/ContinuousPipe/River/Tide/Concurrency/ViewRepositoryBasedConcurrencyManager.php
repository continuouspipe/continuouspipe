<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class ViewRepositoryBasedConcurrencyManager implements TideConcurrencyManager
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideRepository $tideRepository
     */
    public function __construct(TideRepository $tideRepository)
    {
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldTideStart(Tide $tide)
    {
        $tides = $this->tideRepository->findByFlowUuidAndBranch(
            $tide->getFlow()->getUuid(),
            $tide->getCodeReference()->getBranch()
        );

        $runningTides = array_filter($tides, function (Tide $tide) {
            return $tide->getStatus() == Tide::STATUS_RUNNING;
        });

        return count($runningTides) == 0;
    }
}
