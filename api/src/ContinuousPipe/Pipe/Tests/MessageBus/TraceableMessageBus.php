<?php

namespace ContinuousPipe\Pipe\Tests\MessageBus;

use SimpleBus\Message\Bus\MessageBus;

class TraceableMessageBus implements MessageBus
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var MessageBus
     */
    private $messageBus;

    /**
     * @param MessageBus $messageBus
     */
    public function __construct(MessageBus $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message)
    {
        $this->messages[] = $message;

        $this->messageBus->handle($message);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
