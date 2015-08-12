<?php

namespace ContinuousPipe\River\Task\Build\Command;

use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class BuildImagesCommand
{
    /**
     * @var Uuid
     */
    private $tideUuid;
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Uuid $tideUuid
     * @param Log  $log
     */
    public function __construct(Uuid $tideUuid, Log $log)
    {
        $this->tideUuid = $tideUuid;
        $this->log = $log;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }
}
