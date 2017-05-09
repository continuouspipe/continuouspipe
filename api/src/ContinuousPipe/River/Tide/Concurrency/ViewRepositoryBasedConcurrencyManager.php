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
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var int
     */
    private $retryStartInterval;

    /**
     * @param TideRepository    $tideRepository
     * @param MessageBus $commandBus
     * @param int               $retryStartInterval
     */
    public function __construct(TideRepository $tideRepository, MessageBus $commandBus, $retryStartInterval = 60000)
    {
        $this->tideRepository = $tideRepository;
        $this->commandBus = $commandBus;
        $this->retryStartInterval = $retryStartInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldTideStart(Tide $tide)
    {
        $runningTides = $this->tideRepository->findRunningByFlowUuidAndBranch(
            $tide->getFlowUuid(),
            $tide->getCodeReference()->getBranch()
        );

        return count($runningTides) == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function postPoneTideStart(Tide $tide)
    {
        $this->commandBus->handle(new RunPendingTidesCommand(
            $tide->getFlowUuid(),
            $tide->getCodeReference()->getBranch(),
            (new \DateTime())->add(new \DateInterval('PT'.$this->retryStartInterval.'S'))
        ));
    }
}
