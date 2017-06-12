<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Command\PinBranch;
use ContinuousPipe\River\Command\UnpinBranch;
use ContinuousPipe\River\Flow;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class BranchContext implements Context
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @When I pin the branch :branch for the flow :flow
     */
    public function iPinTheBranchForTheFlow($branch, $flow)
    {
        $this->commandBus->handle(new PinBranch(Uuid::fromString($flow), $branch));
    }

    /**
     * @When I unpin the branch :branch for the flow :flow
     */
    public function iUnpinTheBranchForTheFlow($branch, $flow)
    {
        $this->commandBus->handle(new UnpinBranch(Uuid::fromString($flow), $branch));
    }

}
