<?php

namespace ContinuousPipe\River\Task\Wait\Event;

use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class WaitStarted extends WaitEvent
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Uuid   $tideUuid
     * @param Log    $log
     * @param string $taskId
     */
    public function __construct(Uuid $tideUuid, Log $log, $taskId)
    {
        parent::__construct($tideUuid, $taskId);

        $this->log = $log;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }
}
