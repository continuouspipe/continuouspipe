<?php

namespace ContinuousPipe\River\EventBus;

use ContinuousPipe\River\Event\TideEvent;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class TideEventStoreMiddleware implements MessageBusMiddleware
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof TideEvent) {
            $this->eventStore->add($message);
        }

        $next($message);
    }
}
