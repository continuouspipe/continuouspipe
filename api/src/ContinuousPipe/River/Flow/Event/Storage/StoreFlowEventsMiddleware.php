<?php

namespace ContinuousPipe\River\Flow\Event\Storage;

use ContinuousPipe\River\EventStore\EventStore;
use ContinuousPipe\River\Flow\Event\FlowEvent;
use ContinuousPipe\River\Flow\EventStream;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class StoreFlowEventsMiddleware implements MessageBusMiddleware
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
        if ($message instanceof FlowEvent) {
            $this->eventStore->store(EventStream::fromUuid($message->getFlowUuid()), $message);
        }

        $next($message);
    }
}
