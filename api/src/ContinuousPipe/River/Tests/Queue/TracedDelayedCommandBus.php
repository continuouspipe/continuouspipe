<?php

namespace ContinuousPipe\River\Tests\Queue;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;

class TracedDelayedCommandBus implements DelayedCommandBus
{
    /**
     * @var DelayedCommandBus
     */
    private $decoratedProducer;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param DelayedCommandBus $decoratedProducer
     */
    public function __construct(DelayedCommandBus $decoratedProducer)
    {
        $this->decoratedProducer = $decoratedProducer;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($message, $delay)
    {
        $this->messages[] = $message;

        return $this->decoratedProducer->publish($message, $delay);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
