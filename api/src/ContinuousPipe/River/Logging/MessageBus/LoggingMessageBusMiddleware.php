<?php

namespace ContinuousPipe\River\Logging\MessageBus;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class LoggingMessageBusMiddleware implements MessageBusMiddleware
{

    /**
     * The provided $next callable should be called whenever the next middleware should start handling the message.
     * Its only argument should be a Message object (usually the same as the originally provided message).
     *
     * @param object $message
     * @param callable $next
     * @return void
     */
    public function handle($message, callable $next)
    {

    }
}