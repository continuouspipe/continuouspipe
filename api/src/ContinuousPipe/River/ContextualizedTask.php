<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\Task;

class ContextualizedTask implements Task
{
    /**
     * @var Task
     */
    private $task;

    /**
     * @var TideContext
     */
    private $taskContext;

    /**
     * @param Task    $task
     * @param Context $taskContext
     */
    public function __construct(Task $task, Context $taskContext)
    {
        $this->task = $task;
        $this->taskContext = $taskContext;
    }

    /**
     * {@inheritdoc}
     */
    public function start(TideContext $context)
    {
        $context = new TideContext(new ContextTree($this->taskContext, $context));

        return $this->task->start($context);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(TideEvent $event)
    {
        return $this->task->apply($event);
    }

    /**
     * {@inheritdoc}
     */
    public function popNewEvents()
    {
        return $this->task->popNewEvents();
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return $this->task->isRunning();
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->task->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return $this->task->isFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return $this->task->isPending();
    }
}
