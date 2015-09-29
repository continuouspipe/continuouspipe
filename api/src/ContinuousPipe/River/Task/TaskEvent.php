<?php

namespace ContinuousPipe\River\Task;

interface TaskEvent
{
    /**
     * Returns the identifier of the task that created this event or the related
     * task.
     *
     * This is mainly use to ensure that many different tasks of the same type won't
     * conflict.
     *
     * @return string
     */
    public function getTaskId();
}
