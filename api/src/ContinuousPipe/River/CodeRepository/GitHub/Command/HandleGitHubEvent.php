<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Command;

use GitHub\WebHook\Event;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class HandleGitHubEvent
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

    /**
     * @return Uuid
     */
    public function getFlowUuid(): Uuid
    {
        return $this->flowUuid;
    }
}
