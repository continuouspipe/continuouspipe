<?php

namespace ContinuousPipe\River\Task\Wait\Event;

class WaitSuccessful extends WaitEvent
{
    /**
     * @var WaitStarted
     */
    private $waitStarted;

    /**
     * @param WaitStarted $waitStarted
     */
    public function __construct(WaitStarted $waitStarted)
    {
        parent::__construct($waitStarted->getTideUuid(), $waitStarted->getTaskId());

        $this->waitStarted = $waitStarted;
    }

    /**
     * @return WaitStarted
     */
    public function getWaitStarted()
    {
        return $this->waitStarted;
    }
}
