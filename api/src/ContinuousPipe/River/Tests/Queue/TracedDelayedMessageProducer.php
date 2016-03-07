<?php

namespace ContinuousPipe\River\Tests\Queue;

use ContinuousPipe\River\Queue\DelayedMessageProducer;

class TracedDelayedMessageProducer implements DelayedMessageProducer
{
    /**
     * @var DelayedMessageProducer
     */
    private $decoratedProducer;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param DelayedMessageProducer $decoratedProducer
     */
    public function __construct(DelayedMessageProducer $decoratedProducer)
    {
        $this->decoratedProducer = $decoratedProducer;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($message, $delay)
    {
        $this->messages[] = $message;

        return $this->decoratedProducer->queue($message, $delay);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
