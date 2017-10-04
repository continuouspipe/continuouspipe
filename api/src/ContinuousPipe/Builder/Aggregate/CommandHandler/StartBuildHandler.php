<?php

namespace ContinuousPipe\Builder\Aggregate\CommandHandler;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Events\Transaction\TransactionManager;

class StartBuildHandler
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

    public function handle(StartBuild $command)
    {
        $this->transactionManager->apply($command->getBuildIdentifier(), function (Build $build) {
            $build->start();
        });
    }
}
