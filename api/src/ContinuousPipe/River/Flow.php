<?php

namespace ContinuousPipe\River;

class Flow
{
    /**
     * @var Flow\Task[]
     */
    private $tasks;

    /**
     * @var FlowContext
     */
    private $context;

    /**
     * @param FlowContext $context
     * @param Flow\Task[] $tasks
     */
    public function __construct(FlowContext $context, array $tasks)
    {
        $this->tasks = $tasks;
        $this->context = $context;
    }

    /**
     * @return Flow\Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
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
