<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;

class DoActivateFlex
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var TransactionManager
     */
    private $flowTransactionManager;

    public function __construct(
        FlowRepository $flowRepository,
        TransactionManager $flowTransactionManager
    ) {
        $this->flowRepository = $flowRepository;
        $this->flowTransactionManager = $flowTransactionManager;
    }

    public function handle(ActivateFlex $command)
    {
        $flow = $this->flowRepository->find($command->getFlowUuid());

        $this->flowTransactionManager->apply($flow->getUuid()->toString(), function (Flow $flow) {
            $flow->activateFlex();
        });
    }
}
