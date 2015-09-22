<?php

namespace ContinuousPipe\River;

class Flow
{
    /**
     * @var FlowContext
     */
    private $context;

    /**
     * @param FlowContext $context
     */
    public function __construct(FlowContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return FlowContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \Rhumsaa\Uuid\Uuid
     */
    public function getUuid()
    {
        return $this->getContext()->getFlowUuid();
    }
}
