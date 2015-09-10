<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;

interface Task
{
    /**
     * Start the task.
     */
    public function start();

    /**
     * @param TideEvent $event
     */
    public function apply(TideEvent $event);

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
     * @return TideEvent[]
     */
    public function popNewEvents();
}
