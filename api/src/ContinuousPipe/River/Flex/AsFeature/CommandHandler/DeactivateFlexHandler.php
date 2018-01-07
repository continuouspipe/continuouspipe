<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\River\Flex\AsFeature\Command\DeactivateFlex;
use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\River\Flow;

class DeactivateFlexHandler
{
    /**
     * @var TransactionManager
     */
    private $flowTransactionManager;

    public function __construct(TransactionManager $flowTransactionManager)
    {
        $this->flowTransactionManager = $flowTransactionManager;
    }

    public function handle(DeactivateFlex $command)
    {
        $this->flowTransactionManager->apply($command->getFlowUuid(), function (Flow $flow) {
            $flow->deactivateFlex();
        });
    }
}
