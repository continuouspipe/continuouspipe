<?php

namespace ContinuousPipe\River\Tests\Queue;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;

class InMemoryDelayedCommandBus implements DelayedCommandBus
{
    private $messages = [];

    /**
     * {@inheritdoc}
     */
    public function publish($message, $delay)
    {
        $this->messages[] = $message;
    }
}
