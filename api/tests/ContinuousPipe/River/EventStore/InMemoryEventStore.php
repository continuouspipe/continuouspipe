<?php

namespace ContinuousPipe\River\EventStore;

use JMS\Serializer\SerializerInterface;

class InMemoryEventStore implements EventStore
{
    private $streams = [];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function store(string $stream, $event)
    {
        if (!array_key_exists($stream, $this->streams)) {
            $this->streams[$stream] = [];
        }

        $this->streams[$stream][] = [
            'class' => get_class($event),
            'data' => $this->serializer->serialize($event, 'json')
        ];
    }

    public function read(string $stream) : array
    {
        if (!array_key_exists($stream, $this->streams)) {
            return [];
        }

        return array_map(function(array $event) {
            return $this->serializer->deserialize($event['data'], $event['class'], 'json');
        }, $this->streams[$stream]);
    }
}
