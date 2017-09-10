<?php

namespace ContinuousPipe\River\EventStore;

use EventStore\EventStore as EventStoreClient;
use EventStore\Http\GuzzleHttpClient;
use EventStore\WritableEvent;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use JMS\Serializer\SerializerInterface;

class HttpEventStoreAdapter implements EventStore
{
    private $client;
    private $serializer;
    private $eventStoreHost;

    public function __construct(SerializerInterface $serializer, string $eventStoreHost)
    {
        $this->serializer = $serializer;
        $this->eventStoreHost = $eventStoreHost;
    }

    public function store(string $stream, $event)
    {
        $className = get_class($event);
        $name = substr($className, strrpos($className, '\\') + 1);

        $this->client()->writeToStream($stream, WritableEvent::newInstance(
            $name,
            \GuzzleHttp\json_decode($this->serializer->serialize($event, 'json'), true),
            [
                'class' => get_class($event),
            ]
        ));
    }

    public function read(string $stream) : array
    {
        $iterator = $this->client()->forwardStreamFeedIterator($stream);
        $events = [];

        foreach ($iterator as $entryWithEvent) {
            /** @var \EventStore\StreamFeed\EntryWithEvent $entryWithEvent */
            $event = $entryWithEvent->getEvent();

            $events[] = $this->serializer->deserialize(
                \GuzzleHttp\json_encode($event->getData()),
                $event->getMetadata()['class'],
                'json'
            );
        }

        return $events;
    }

    private function client()
    {
        if (null === $this->client) {
            $this->client = new EventStoreClient('http://'.$this->eventStoreHost.':2113', new GuzzleHttpClient(
                new Client([
                    'connect_timeout' => 1,
                    'read_timeout' => 5,
                    'timeout' => 10,
                    'handler' => new CurlMultiHandler(),
                ])
            ));
        }

        return $this->client;
    }
}
