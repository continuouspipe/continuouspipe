<?php

namespace ContinuousPipe\River\CommandBus;

interface DelayedCommandBus
{
    /**
     * Publish a delayed command.
     *
     * The delay is a milliseconds amount.
     *
     * @param object $command
     * @param int    $delay
     */
    public function publish($command, $delay);
}
