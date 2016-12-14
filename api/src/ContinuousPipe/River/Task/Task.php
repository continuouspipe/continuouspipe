<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use LogStream\Log;

interface Task
{
    const STATUS_PENDING = 'pending';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_RUNNING = 'running';
    const STATUS_FAILED = 'failed';
    const STATUS_SUCCESSFUL = 'successful';

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
     * Get the status of the given task. This can be one of the status
     * defined as constant of this interface.
     *
     * @return string
     */
    public function getStatus() : string;

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
