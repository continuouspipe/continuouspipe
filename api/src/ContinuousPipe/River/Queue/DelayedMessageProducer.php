<?php

namespace ContinuousPipe\River\Queue;

interface DelayedMessageProducer
{
    /**
     * @param object $message
     * @param int    $delay
     */
    public function queue($message, $delay);
}
