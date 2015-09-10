<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\Runner\Client\RunNotification;
use Rhumsaa\Uuid\Uuid;

class RunSuccessful implements TideEvent, RunEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var RunNotification
     */
    private $run;

    /**
     * @param Uuid            $tideUuid
     * @param RunNotification $run
     */
    public function __construct(Uuid $tideUuid, RunNotification $run)
    {
        $this->tideUuid = $tideUuid;
        $this->run = $run;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return RunNotification
     */
    public function getRun()
    {
        return $this->run;
    }

    /**
     * {@inheritdoc}
     */
    public function getRunUuid()
    {
        return Uuid::fromString($this->run->getUuid());
    }
}
