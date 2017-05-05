<?php

namespace ContinuousPipe\River\EventStore;

use Psr\Log\LoggerInterface;

class FallbackToNonEmptyEventStore implements EventStore
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventStore
     */
    private $fallback;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventStore $eventStore
     * @param EventStore $fallback
     * @param LoggerInterface $logger
     */
    public function __construct(EventStore $eventStore, EventStore $fallback, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->fallback = $fallback;
        $this->logger = $logger;
    }

    public function store(string $stream, $event)
    {
        $this->eventStore->store($stream, $event);

        try {
            $this->fallback->store($stream, $event);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to store events in fallback', [
                'stream' => $stream,
            ]);
        }
    }

    public function read(string $stream): array
    {
        if (empty($events = $this->eventStore->read($stream))) {
            try {
                $events = $this->fallback->read($stream);
            } catch (\Throwable $e) {
                $this->logger->warning('Unable to get events from fallback, return 0 events', [
                    'stream' => $stream,
                ]);

                $events = [];
            }
        }

        return $events;
    }
}
