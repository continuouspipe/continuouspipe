<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\Flow;

class FlowCreated
{
    /**
     * @var Flow
     */
    private $flow;

    /**
     * @param Flow $flow
     */
    public function __construct(Flow $flow)
    {
        $this->flow = $flow;
    }

    /**
     * @return Flow
     */
    public function getFlow()
    {
        return $this->flow;
    }
}
