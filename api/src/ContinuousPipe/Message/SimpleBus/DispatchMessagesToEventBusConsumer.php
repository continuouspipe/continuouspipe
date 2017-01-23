<?php

namespace ContinuousPipe\Message\SimpleBus;

use ContinuousPipe\Message\Message;
use ContinuousPipe\Message\MessageConsumer;
use SimpleBus\Message\Bus\MessageBus;

class DispatchMessagesToEventBusConsumer implements MessageConsumer
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
     * {@inheritdoc}
     */
    public function consume(Message $message)
    {
        $this->eventBus->handle($message);
    }
}
