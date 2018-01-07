<?php

namespace ContinuousPipe\River\Event;

class TideEventWithMetadata
{
    /**
     * @var TideEvent
     */
    private $tideEvent;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @param TideEvent $tideEvent
     * @param \DateTime $dateTime
     */
    public function __construct(TideEvent $tideEvent, \DateTime $dateTime)
    {
        $this->tideEvent = $tideEvent;
        $this->dateTime = $dateTime;
    }

    /**
     * @return TideEvent
     */
    public function getTideEvent()
    {
        return $this->tideEvent;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
