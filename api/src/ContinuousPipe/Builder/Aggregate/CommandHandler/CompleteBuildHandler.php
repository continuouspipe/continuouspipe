<?php

namespace ContinuousPipe\Builder\Aggregate\CommandHandler;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\Command\CompleteBuild;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Events\Transaction\TransactionManager;

class CompleteBuildHandler
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

    public function handle(CompleteBuild $command)
    {
        $status = $command->getStatus();
        $this->transactionManager->apply($command->getBuildIdentifier(), function (Build $build) use ($status) {
            $build->completeBuild($status);
        });
    }
}
