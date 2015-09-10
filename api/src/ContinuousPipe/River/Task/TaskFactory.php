<?php

namespace ContinuousPipe\River\Task;

interface TaskFactory
{
    /**
     * @param TaskContext $taskContext
     *
     * @return Task
     */
    public function create(TaskContext $taskContext);
}
