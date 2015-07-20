<?php

namespace ContinuousPipe\River\EventBus;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\TideSaga;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class TideSagaApplyMiddleware implements MessageBusMiddleware
{
    /**
     * @var TideSaga
     */
    private $tideSaga;

    /**
     * @param TideSaga $tideSaga
     */
    public function __construct(TideSaga $tideSaga)
    {
        $this->tideSaga = $tideSaga;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof TideEvent) {
            $this->tideSaga->notify($message);
        }

        $next($message);
    }
}
