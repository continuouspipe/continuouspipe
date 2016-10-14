<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Serializer;

use GitHub\WebHook\Event;

class WrappedGitHubEvent implements Event
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'wrapped';
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }
}
