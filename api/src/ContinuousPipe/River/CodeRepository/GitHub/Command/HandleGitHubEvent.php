<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Command;

use ContinuousPipe\River\Flow\Event\FlowEvent;
use GitHub\WebHook\Event;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class HandleGitHubEvent implements FlowEvent
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @JMS\Type("GitHub\WebHook\AbstractEvent")
     *
     * @var Event
     */
    private $event;

    /**
     * @param Uuid  $flowUuid
     * @param Event $event
     */
    public function __construct(Uuid $flowUuid, Event $event)
    {
        $this->flowUuid = $flowUuid;
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
