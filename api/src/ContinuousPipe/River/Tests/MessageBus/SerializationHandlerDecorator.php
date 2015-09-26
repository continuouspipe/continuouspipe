<?php

namespace ContinuousPipe\River\Tests\MessageBus;

use SimpleBus\Serialization\ObjectSerializer;

class SerializationHandlerDecorator
{
    /**
     * @var ObjectSerializer
     */
    private $objectSerializer;

    /**
     * @var object
     */
    private $handler;

    /**
     * @param ObjectSerializer $objectSerializer
     * @param object           $handler
     */
    public function __construct(ObjectSerializer $objectSerializer, $handler)
    {
        $this->objectSerializer = $objectSerializer;
        $this->handler = $handler;
    }

    /**
     * @param object $command
     */
    public function handle($command)
    {
        $serialized = $this->objectSerializer->serialize($command);
        $command = $this->objectSerializer->deserialize($serialized, get_class($command));

        $this->handler->handle($command);
    }
}
