<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class TideCreated implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;
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
     * @param Uuid          $tideUuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     * @param Log           $parentLog
     */
    public function __construct(Uuid $tideUuid, Flow $flow, CodeReference $codeReference, Log $parentLog)
    {
        $this->tideUuid = $tideUuid;
        $this->flow = $flow;
        $this->codeReference = $codeReference;
        $this->parentLog = $parentLog;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
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
     * @return Log
     */
    public function getParentLog()
    {
        return $this->parentLog;
    }
}
