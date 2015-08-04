<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class StartTideCommand
{
    /**
     * @var Uuid
     */
    private $uuid;
    /**
     * @var Flow
     */
    private $flow;
    /**
     * @var CodeReference
     */
    private $codeReference;
    /**
     * @var Log
     */
    private $parentLog;

    /**
     * @param Uuid $uuid
     * @param Flow $flow
     * @param CodeReference $codeReference
     * @param Log $parentLog
     */
    public function __construct(Uuid $uuid, Flow $flow, CodeReference $codeReference, Log $parentLog)
    {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
        $this->uuid = $uuid;
        $this->parentLog = $parentLog;
    }

    /**
     * @return Flow
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Log
     */
    public function getParentLog()
    {
        return $this->parentLog;
    }
}
