<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use LogStream\Log;

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

    public function getIdentifier() : string;
    public function getLogIdentifier() : string;
    public function getLabel() : string;

    /**
     * @return ArrayObject
     */
    public function getExposedContext();
}
