<?php

namespace ContinuousPipe\Events\SimpleBus;

use ContinuousPipe\Events\Event;
use ContinuousPipe\Events\EventStore\EventStore;
use ContinuousPipe\Events\EventStore\EventStreamResolver;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class StoreEventsMiddleware implements MessageBusMiddleware
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventStreamResolver
     */
    private $eventStreamResolver;

    /**
     * @param EventStore $eventStore
     * @param EventStreamResolver $eventStreamResolver
     */
    public function __construct(EventStore $eventStore, EventStreamResolver $eventStreamResolver)
    {
        $this->eventStore = $eventStore;
        $this->eventStreamResolver = $eventStreamResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        if (null !== ($stream = $this->eventStreamResolver->streamByEvent($message))) {
            $this->eventStore->store($stream, $message);
        }

        $next($message);
    }
}
