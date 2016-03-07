<?php

namespace ContinuousPipe\River\Tests\Queue;

use ContinuousPipe\River\Queue\DelayedMessageProducer;

class InMemoryDelayedMessageProducer implements DelayedMessageProducer
{
    private $messages = [];

    /**
     * {@inheritdoc}
     */
    public function queue($message, $delay)
    {
        $this->messages[] = $message;
    }
}
