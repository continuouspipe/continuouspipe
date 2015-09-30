<?php

namespace GitHub\WebHook;

use GitHub\WebHook\Security\InvalidRequest;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestDeserializer
{
    const HEADER_EVENT = 'X-GitHub-Event';
    const HEADER_DELIVERY = 'X-GitHub-Delivery';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EventClassMapping
     */
    private $eventClassMapping;

    public function __construct(SerializerInterface $serializer, EventClassMapping $eventClassMapping)
    {
        $this->serializer = $serializer;
        $this->eventClassMapping = $eventClassMapping;
    }

    /**
     * @param Request $request
     *
     * @throws EventClassNotFound
     * @throws InvalidRequest
     *
     * @return Event
     */
    public function deserialize(Request $request)
    {
        $event = $request->headers->get(self::HEADER_EVENT);
        if (null === $event) {
            throw new InvalidRequest('Unable to determine which event');
        }

        $className = $this->eventClassMapping->getEventClass($event);
        $payload = $request->getContent();

        return $this->serializer->deserialize($payload, $className, 'json');
    }
}
