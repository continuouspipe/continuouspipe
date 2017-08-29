<?php

namespace ContinuousPipe\River\Notifications\Asynchronous\Notifier;

use ContinuousPipe\River\Notifications\Asynchronous\Command\NotifyCommand;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use SimpleBus\Message\Bus\MessageBus;

class TransformCallsToCommand implements Notifier
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var Notifier
     */
    private $decoratedNotifier;

    public function __construct(MessageBus $commandBus, Notifier $decoratedNotifier)
    {
        $this->commandBus = $commandBus;
        $this->decoratedNotifier = $decoratedNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        $this->commandBus->handle(new NotifyCommand($tide->getUuid(), $status, $configuration));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $this->decoratedNotifier->supports($tide, $status, $configuration);
    }
}
