<?php

namespace ContinuousPipe\River\Analytics\Logitio\Asynchronous\Command;

use ContinuousPipe\Message\Message;
use ContinuousPipe\River\Message\OperationalMessage;
use JMS\Serializer\Annotation as JMS;

class AddEventCommand implements OperationalMessage
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $logType;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $event;

    public function __construct(string $logType, array $event)
    {
        $this->logType = $logType;
        $this->event = $event;
    }

    public function getLogType(): string
    {
        return $this->logType;
    }

    public function getEvent(): array
    {
        return $this->event;
    }
}
