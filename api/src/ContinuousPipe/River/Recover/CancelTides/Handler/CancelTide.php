<?php

namespace ContinuousPipe\River\Recover\CancelTides\Handler;

use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Recover\CancelTides\Command\CancelTideCommand;
use ContinuousPipe\River\Recover\CancelTides\Event\TideCancelled;
use SimpleBus\Message\Bus\MessageBus;

class CancelTide
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param CancelTideCommand $command
     */
    public function handle(CancelTideCommand $command)
    {
        $this->eventBus->handle(new TideCancelled($command->getTideUuid()));
        $this->eventBus->handle(new TideFailed($command->getTideUuid()));
    }
}
