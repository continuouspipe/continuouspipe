<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class BuildImageCommand
{
    /**
     * @var BuildRequest
     */
    private $buildRequest;
    /**
     * @var Uuid
     */
    private $tideUuid;
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Uuid         $tideUuid
     * @param BuildRequest $buildRequest
     * @param Log          $log
     */
    public function __construct(Uuid $tideUuid, BuildRequest $buildRequest, Log $log)
    {
        $this->buildRequest = $buildRequest;
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
     * @return BuildRequest
     */
    public function getBuildRequest()
    {
        return $this->buildRequest;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }
}
