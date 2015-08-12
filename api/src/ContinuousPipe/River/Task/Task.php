<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\TideContext;

interface Task
{
    /**
     * @param TideEvent $event
     */
    public function apply(TideEvent $event);

    /**
     * @return TideEvent[]
     */
    public function popNewEvents();

    /**
     * @param TideContext $context
     */
    public function start(TideContext $context);

    /**
     * Is this task running ?
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Is this task successful ?
     *
     * @return bool
     */
    public function isSuccessful();

    /**
     * Is this task failed ?
     *
     * @return bool
     */
    public function isFailed();

    /**
     * @return bool
     */
    public function isPending();

    /**
     * Clear the state of the task, as it's being reused.
     */
    public function clear();
}
