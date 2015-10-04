<?php

namespace ContinuousPipe\River\Task;

use ContinuousPipe\River\Tide;

interface TaskRunner
{
    /**
     * Run the given task.
     *
     * @param Tide $tide
     * @param Task $task
     *
     * @throws TaskRunnerException
     */
    public function run(Tide $tide, Task $task);
}
