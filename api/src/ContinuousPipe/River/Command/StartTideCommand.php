<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;

class StartTideCommand
{
    /**
     * @var Flow
     */
    private $flow;
    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     */
    public function __construct(Flow $flow, CodeReference $codeReference)
    {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
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
