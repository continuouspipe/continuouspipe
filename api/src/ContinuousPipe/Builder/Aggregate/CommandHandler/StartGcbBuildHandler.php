<?php

namespace ContinuousPipe\Builder\Aggregate\CommandHandler;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\Command\StartGcbBuild;
use ContinuousPipe\Events\Transaction\TransactionManager;

class StartGcbBuildHandler
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

    public function handle(StartGcbBuild $command)
    {
        $this->transactionManager->apply($command->getBuildIdentifier(), function (Build $build) {
            $build->start();
        });
    }
}
