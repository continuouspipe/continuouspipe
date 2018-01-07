<?php

namespace GitHub\WebHook;

class GitHubRequest
{
    /**
     * @var string
     */
    private $deliveryId;

    /**
     * @var Event
     */
    private $event;

    /**
     * @param string $deliveryId
     * @param Event  $event
     */
    public function __construct($deliveryId, Event $event)
    {
        $this->deliveryId = $deliveryId;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
