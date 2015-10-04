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
     * Returns true if this task accepts the given event.
     *
     * @param TideEvent $event
     *
     * @return bool
     */
    public function accept(TideEvent $event);

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
     * @return bool
     */
    public function isSkipped();

    /**
     * @return TideEvent[]
     */
    public function popNewEvents();

    /**
     * @return TaskContext
     */
    public function getContext();
}
