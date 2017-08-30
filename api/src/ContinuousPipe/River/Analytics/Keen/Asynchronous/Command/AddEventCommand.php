<?php

namespace ContinuousPipe\River\Analytics\Keen\Asynchronous\Command;

use ContinuousPipe\Message\Message;
use JMS\Serializer\Annotation as JMS;

class AddEventCommand implements Message
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $collection;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $event;

    public function __construct(string $collection, array $event)
    {
        $this->collection = $collection;
        $this->event = $event;
    }

    public function getCollection(): string
    {
        return $this->collection;
    }

    public function getEvent(): array
    {
        return $this->event;
    }
}
