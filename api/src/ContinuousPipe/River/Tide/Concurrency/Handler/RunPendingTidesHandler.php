<?php

namespace ContinuousPipe\River\Tide\Concurrency\Handler;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Tide\Concurrency\Command\RunPendingTidesCommand;
use ContinuousPipe\River\Tide\Concurrency\TideConcurrencyManager;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use SimpleBus\Message\Bus\MessageBus;

class RunPendingTidesHandler
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var TideConcurrencyManager
     */
    private $tideConcurrencyManager;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param TideRepository         $tideRepository
     * @param TideConcurrencyManager $tideConcurrencyManager
     * @param MessageBus             $commandBus
     */
    public function __construct(TideRepository $tideRepository, TideConcurrencyManager $tideConcurrencyManager, MessageBus $commandBus)
    {
        $this->tideRepository = $tideRepository;
        $this->tideConcurrencyManager = $tideConcurrencyManager;
        $this->commandBus = $commandBus;
    }

    /**
     * @param RunPendingTidesCommand $command
     */
    public function handle(RunPendingTidesCommand $command)
    {
        $pendingTides = $this->tideRepository->findPendingByFlowUuidAndBranch($command->getFlowUuid(), $command->getBranch());
        usort($pendingTides, function (Tide $left, Tide $right) {
            return $left->getCreationDate() > $right->getCreationDate() ? 1 : -1;
        });

        /** @var Tide $nextTide */
        if (null === ($nextTide = array_shift($pendingTides))) {
            return;
        }

        $this->commandBus->handle(new StartTideCommand($nextTide->getUuid()));
    }
}
