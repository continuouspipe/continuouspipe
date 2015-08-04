<?php

namespace ContinuousPipe\River\Logging\CommandBus;

use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class ReturnSuccessAndFailureEventsMiddleware implements MessageBusMiddleware
{

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $result = $next();


    }
}
