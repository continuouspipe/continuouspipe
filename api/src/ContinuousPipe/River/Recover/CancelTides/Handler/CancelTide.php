<?php

namespace ContinuousPipe\River\Recover\CancelTides\Handler;

use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Recover\CancelTides\Command\CancelTideCommand;
use ContinuousPipe\River\Event\TideCancelled;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Transaction\TransactionManager;
use SimpleBus\Message\Bus\MessageBus;

class CancelTide
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param CancelTideCommand $command
     */
    public function handle(CancelTideCommand $command)
    {
        $this->transactionManager->apply($command->getTideUuid(), function (Tide $tide) use ($command) {
            $tide->cancel($command->getUsername());
        });
    }
}
