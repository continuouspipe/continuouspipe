<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
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
     * @param Uuid          $uuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     */
    public function __construct(Uuid $uuid, Flow $flow, CodeReference $codeReference)
    {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
        $this->uuid = $uuid;
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
}
