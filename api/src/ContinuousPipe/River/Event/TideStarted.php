<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use Rhumsaa\Uuid\Uuid;

class TideStarted implements TideEvent
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
     * @param Uuid          $tideUuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     */
    public function __construct(Uuid $tideUuid, Flow $flow, CodeReference $codeReference)
    {
        $this->tideUuid = $tideUuid;
        $this->flow = $flow;
        $this->codeReference = $codeReference;
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
}
